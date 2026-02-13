<?php

namespace App\Console\Commands;

use App\Models\Employee;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class InsertEmployees extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'employees:insert {count=50}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Insert multiple employees with progress bar';

    /**
     * Execute the console command.
     */

    public function handle()
    {
        $count = (int) $this->argument('count');

        if ($count <= 0) {
            $this->error('Count must be greater than zero.');
            return Command::FAILURE;
        }

        $this->info("Inserting {$count} employees...");

        $bar = $this->output->createProgressBar($count);
        $bar->start();

        DB::transaction(function () use ($count, $bar) {
            for ($i = 0; $i < $count; $i++) {
                Employee::factory()->create();
                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine(2);

        $this->info("{$count} employees inserted successfully.");

        return Command::SUCCESS;
    }

}
