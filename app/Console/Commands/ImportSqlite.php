<?php

namespace App\Console\Commands;

use App\Support\Database\DatabaseBackup;
use App\Support\Database\SqliteToMysql;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportSqlite extends Command
{
    protected $signature = 'serenovia:import-sqlite {source : Absolute path to the existing SQLite database}';
    protected $description = 'Copy and verify SQLite data into a completely empty MySQL database';

    public function handle(SqliteToMysql $importer, DatabaseBackup $backup): int
    {
        $path = realpath($this->argument('source'));
        if (! $path || ! is_file($path)) {
            $this->error('SQLite source file not found.');
            return self::FAILURE;
        }
        config(['database.connections.migration_source' => [
            'driver' => 'sqlite', 'database' => $path, 'prefix' => '', 'foreign_key_constraints' => true,
        ]]);
        try {
            $snapshot = $backup->create(DB::connection('migration_source'), storage_path('app/backups'));
            $this->info('SQLite snapshot saved outside the web root.');
            $counts = $importer->import(DB::connection(), $snapshot, function () {
                if ($this->call('migrate', ['--force' => true]) !== 0) {
                    throw new \RuntimeException('MySQL schema migration failed.');
                }
            });
            foreach ($counts as $table => $count) {
                $this->line("Verified {$table}: {$count} rows");
            }
            $this->info('MySQL import complete. Original SQLite database retained.');
            return self::SUCCESS;
        } catch (\Illuminate\Database\QueryException $error) {
            // Keep copied account/customer values out of the hosting deployment log.
            $state = $error->errorInfo[0] ?? 'unknown';
            $this->error("Database import failed (SQLSTATE {$state}). The environment was not switched. Check schema compatibility and database permissions.");
            return self::FAILURE;
        } finally {
            DB::purge('migration_source');
        }
    }
}
