<?php

namespace App\Support\Database;

use Illuminate\Database\Connection;
use PDO;
use RuntimeException;

class SqliteToMysql
{
    public function import(Connection $target, string $sourcePath, callable $migrate): array
    {
        if (! in_array($target->getDriverName(), ['mysql', 'mariadb'], true)) {
            throw new RuntimeException('The target must be MySQL or MariaDB.');
        }
        if (! is_file($sourcePath)) {
            throw new RuntimeException('The SQLite source file does not exist.');
        }
        // Never overwrite a populated or partially migrated destination.
        if ($target->getSchemaBuilder()->getTables($target->getDatabaseName()) !== [] || $target->getSchemaBuilder()->getViews($target->getDatabaseName()) !== []) {
            throw new RuntimeException('The target database must be completely empty. Use a new database; no existing data was changed.');
        }
        $source = new PDO('sqlite:'.$sourcePath, null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $source->exec('PRAGMA query_only = ON');
        if ($source->query('PRAGMA integrity_check')->fetchColumn() !== 'ok' ||
            $source->query('PRAGMA foreign_key_check')->fetch() !== false) {
            throw new RuntimeException('SQLite integrity or foreign-key checks failed.');
        }
        $tables = $source->query("SELECT name FROM sqlite_master WHERE type = 'table' AND name NOT LIKE 'sqlite_%' AND name != 'migrations' ORDER BY name")
            ->fetchAll(PDO::FETCH_COLUMN);
        if (! in_array('tour_packages', $tables, true) || ! in_array('users', $tables, true)) {
            throw new RuntimeException('The source is not a Serenovia database.');
        }

        $migrate();
        $schema = $target->getSchemaBuilder();
        $metadata = [];
        foreach ($tables as $table) {
            if (! $schema->hasTable($table)) {
                throw new RuntimeException("Source table {$table} is not present in the MySQL schema.");
            }
            $columns = $source->query('PRAGMA table_info('.$this->identifier($table).')')->fetchAll(PDO::FETCH_ASSOC);
            $destination = array_column($schema->getColumns($table), null, 'name');
            foreach ($columns as $column) {
                if (! isset($destination[$column['name']])) {
                    throw new RuntimeException("Source column {$table}.{$column['name']} is not present in MySQL.");
                }
            }
            $metadata[$table] = $destination;
        }
        foreach ($schema->getTables($target->getDatabaseName()) as $table) {
            if (strtolower($table['engine'] ?? '') !== 'innodb') {
                throw new RuntimeException('All target tables must use InnoDB for safe transactional import.');
            }
        }

        $counts = [];
        $source->beginTransaction();
        $target->statement('SET FOREIGN_KEY_CHECKS=0');
        try {
            $target->transaction(function () use ($target, $source, $tables, $metadata, &$counts) {
                foreach ($tables as $table) {
                    // Only migration-created defaults exist: target was empty before migrate.
                    $target->table($table)->delete();
                    $cursor = $source->query('SELECT * FROM '.$this->identifier($table));
                    $count = 0;
                    while ($row = $cursor->fetch(PDO::FETCH_ASSOC)) {
                        $target->table($table)->insert($row);
                        $count++;
                    }
                    $counts[$table] = $count;
                    if ($target->table($table)->count() !== $count) {
                        throw new RuntimeException("Row-count verification failed for {$table}.");
                    }
                    $primary = collect($target->getSchemaBuilder()->getIndexes($table))->firstWhere('primary', true);
                    if (! $primary) {
                        throw new RuntimeException("Cannot verify {$table}: primary key missing.");
                    }
                    $cursor = $source->query('SELECT * FROM '.$this->identifier($table));
                    while ($row = $cursor->fetch(PDO::FETCH_ASSOC)) {
                        $query = $target->table($table);
                        foreach ($primary['columns'] as $key) {
                            $query->where($key, $row[$key]);
                        }
                        $saved = (array) $query->first();
                        foreach ($row as $key => $value) {
                            if ($this->normalize($value, $metadata[$table][$key]['type_name']) !==
                                $this->normalize($saved[$key] ?? null, $metadata[$table][$key]['type_name'])) {
                                throw new RuntimeException("Value verification failed for {$table}.{$key}; import rolled back.");
                            }
                        }
                    }
                }
                // MySQL does not re-check imported rows when foreign keys are re-enabled.
                foreach ($tables as $table) {
                    foreach ($target->getSchemaBuilder()->getForeignKeys($table) as $foreign) {
                        $query = $target->table($table.' as child')->leftJoin($foreign['foreign_table'].' as parent', function ($join) use ($foreign) {
                            foreach ($foreign['columns'] as $index => $column) {
                                $join->on('child.'.$column, '=', 'parent.'.$foreign['foreign_columns'][$index]);
                            }
                        });
                        foreach ($foreign['columns'] as $column) {
                            $query->whereNotNull('child.'.$column);
                        }
                        if ($query->whereNull('parent.'.$foreign['foreign_columns'][0])->exists()) {
                            throw new RuntimeException("Foreign-key verification failed for {$table}.");
                        }
                    }
                }
            });
        } finally {
            $target->statement('SET FOREIGN_KEY_CHECKS=1');
            $source->rollBack();
        }

        return $counts;
    }

    private function identifier(string $name): string
    {
        return '"'.str_replace('"', '""', $name).'"';
    }

    private function normalize(mixed $value, string $type): mixed
    {
        if ($value === null) {
            return null;
        }
        if ($type === 'json') {
            return $this->canonicalJson(json_decode((string) $value, true, 512, JSON_THROW_ON_ERROR));
        }
        if (in_array($type, ['decimal', 'numeric', 'float', 'double', 'real'], true)) {
            $value = (string) $value;
            return str_contains($value, '.') ? rtrim(rtrim($value, '0'), '.') : $value;
        }
        return (string) $value;
    }

    private function canonicalJson(mixed $value): mixed
    {
        if (is_array($value)) {
            if (! array_is_list($value)) {
                ksort($value);
            }
            $value = array_map(fn ($item) => $this->canonicalJson($item), $value);
        }
        return $value;
    }
}
