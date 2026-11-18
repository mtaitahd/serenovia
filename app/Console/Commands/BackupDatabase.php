<?php

namespace App\Console\Commands;

use App\Support\Database\DatabaseBackup;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BackupDatabase extends Command
{
    protected $signature = 'serenovia:backup-database';
    protected $description = 'Create a private consistent backup of the configured database';

    public function handle(DatabaseBackup $backup): int
    {
        $path = $backup->create(DB::connection(), storage_path('app/backups'));
        $this->info('Database backup saved: '.basename($path));
        return self::SUCCESS;
    }
}
