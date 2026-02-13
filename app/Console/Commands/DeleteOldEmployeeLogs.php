<?php

namespace App\Console\Commands;

use App\Models\EmployeeLog;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DeleteOldEmployeeLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'employee-logs:clean {days=30}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete employee logs older than given days';


    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = (int) $this->argument('days');

        if ($days <= 0) {
            $this->error('Days must be greater than zero.');
            return Command::FAILURE;
        }

        $date = Carbon::now()->subDays($days);

        $this->info("Deleting logs older than {$days} days...");

        $totalCount = EmployeeLog::where('created_at', '<=', $date)->count();

        if ($totalCount === 0) {
            $this->info('No logs to delete.');
            return Command::SUCCESS;
        }

        $bar = $this->output->createProgressBar($totalCount);
        $bar->start();

        $deletedCount = 0;

        EmployeeLog::where('created_at', '<=', $date)
            ->orderBy('id')
            ->chunkById(100, function ($logs) use ($bar, &$deletedCount) {
                DB::transaction(function () use ($logs, &$deletedCount) {
                    foreach ($logs as $log) {
                        $log->delete();
                        $deletedCount++;
                    }
                });

                $bar->advance($logs->count());
            });

        $bar->finish();
        $this->newLine(2);

        $this->info("{$deletedCount} logs deleted successfully.");

        return Command::SUCCESS;
    }
}
