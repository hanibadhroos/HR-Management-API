<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ExportDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:export';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export the database to an SQL file';

    /**
     * Execute the console command.
     */

    public function handle()
    {
        $database = config('database.connections.mysql.database');
        $username = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');
        $host = config('database.connections.mysql.host');

        $backupPath = storage_path('app/backups');

        if (!File::exists($backupPath)) {
            File::makeDirectory($backupPath, 0755, true);
        }

        $fileName = 'backup_' . now()->format('Y_m_d_His') . '.sql';
        $filePath = $backupPath . '/' . $fileName;

        $this->info('Exporting database...');

        $mysqlDumpPath = 'C:\\xampp\\mysql\\bin\\mysqldump.exe';

        $port = config('database.connections.mysql.port', '3306');

        $command = sprintf(
            '"%s" --user=%s --password=%s --host=%s --port=%s --protocol=TCP %s > "%s"',
            $mysqlDumpPath,
            $username,
            $password,
            $host,
            $port,
            $database,
            $filePath
        );

        system($command, $result);

        if ($result === 0) {
            $this->info("Database exported successfully to:");
            $this->line($filePath);
            return Command::SUCCESS;
        }

        $this->error('Database export failed.');
        return Command::FAILURE;
    }
}
