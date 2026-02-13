<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ClearLogFiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'logs:clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove all log files from storage/logs directory';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $logPath = storage_path('logs');

        if (!File::exists($logPath)) {
            $this->error('Logs directory does not exist.');
            return Command::FAILURE;
        }

        ///// Remove only .log files.
        $files = collect(File::files($logPath))
            ->filter(fn ($file) => $file->getExtension() === 'log');


        $count = count($files);

        if ($count === 0) {
            $this->info('No log files found.');
            return Command::SUCCESS;
        }

        $this->info("Deleting {$count} log files...");

        $bar = $this->output->createProgressBar($count);
        $bar->start();

        foreach ($files as $file) {
            File::delete($file->getPathname());
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info('All log files removed successfully.');

        return Command::SUCCESS;
    }

}
