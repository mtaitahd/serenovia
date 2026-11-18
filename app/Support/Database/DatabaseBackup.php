<?php

namespace App\Support\Database;

use Illuminate\Database\Connection;
use RuntimeException;
use Throwable;

class DatabaseBackup
{
    public function create(Connection $db, string $directory): string
    {
        if (! is_dir($directory) && ! mkdir($directory, 0700, true) && ! is_dir($directory)) {
            throw new RuntimeException('Cannot create private database backup directory.');
        }
        $base = $directory.'/pre-deploy-'.gmdate('Ymd-His').'-'.bin2hex(random_bytes(6));
        if ($db->getDriverName() === 'sqlite') {
            $path = $base.'.sqlite';
            $db->getPdo()->exec('PRAGMA busy_timeout=5000');
            $db->getPdo()->exec('VACUUM INTO '.$db->getPdo()->quote($path));
            chmod($path, 0600);
            return $path;
        }
        if (! in_array($db->getDriverName(), ['mysql', 'mariadb'], true)) {
            throw new RuntimeException('Unsupported database backup driver.');
        }

        $tables = $db->getSchemaBuilder()->getTables($db->getDatabaseName());
        foreach ($tables as $table) {
            if (strtolower($table['engine'] ?? '') !== 'innodb') {
                throw new RuntimeException('Backup requires InnoDB tables.');
            }
        }
        $path = $base.'.sql';
        $file = fopen($path.'.partial', 'xb');
        if (! $file) {
            throw new RuntimeException('Cannot open database backup file.');
        }
        chmod($path.'.partial', 0600);
        $write = static function (string $text) use ($file): void {
            if (fwrite($file, $text) !== strlen($text)) {
                throw new RuntimeException('Incomplete database backup write.');
            }
        };
        $quote = static fn (string $name): string => '`'.str_replace('`', '``', $name).'`';
        try {
            $db->statement('SET TRANSACTION ISOLATION LEVEL REPEATABLE READ');
            $db->beginTransaction();
            $write("-- Serenovia MySQL backup; import into an EMPTY database.\nSET NAMES utf8mb4;\nSET @OLD_SQL_MODE=@@SQL_MODE;\nSET SQL_MODE='NO_AUTO_VALUE_ON_ZERO';\nSET FOREIGN_KEY_CHECKS=0;\n");
            foreach ($tables as $table) {
                $name = $table['name'];
                $create = (array) $db->selectOne('SHOW CREATE TABLE '.$quote($name));
                $write($create['Create Table'].";\n");
                foreach ($db->table($name)->cursor() as $row) {
                    $row = (array) $row;
                    $values = array_map(static function ($value): string {
                        if ($value === null) {
                            return 'NULL';
                        }
                        // Explicit dump SQL mode makes escaping independent of server defaults.
                        return "'".str_replace(["\\", "'", "\0", "\n", "\r", "\x1a"], ["\\\\", "''", "\\0", "\\n", "\\r", "\\Z"], (string) $value)."'";
                    }, array_values($row));
                    $write('INSERT INTO '.$quote($name).' ('.implode(',', array_map($quote, array_keys($row))).') VALUES ('.implode(',', $values).");\n");
                }
            }
            $write("SET FOREIGN_KEY_CHECKS=1;\nSET SQL_MODE=@OLD_SQL_MODE;\n");
            $db->commit();
            if (! fflush($file)) {
                throw new RuntimeException('Could not flush database backup.');
            }
            fclose($file);
            if (! rename($path.'.partial', $path)) {
                throw new RuntimeException('Could not finalize database backup.');
            }
        } catch (Throwable $error) {
            if ($db->transactionLevel() > 0) {
                $db->rollBack();
            }
            if (is_resource($file)) {
                fclose($file);
            }
            @unlink($path.'.partial');
            throw $error;
        }
        return $path;
    }
}
