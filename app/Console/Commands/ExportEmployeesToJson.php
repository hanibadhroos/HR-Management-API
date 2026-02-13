<?php

namespace App\Console\Commands;

use App\Models\Employee;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ExportEmployeesToJson extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'employees:export-json';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export all employees to a JSON file';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Fetching employees...');

        $employees = Employee::with(['manager', 'position'])->get();

        $count = $employees->count();

        if ($count === 0) {
            $this->warn('No employees found.');
            return Command::SUCCESS;
        }

        $exportPath = storage_path('app/exports');

        if (!File::exists($exportPath)) {
            File::makeDirectory($exportPath, 0755, true);
        }

        $fileName = 'employees_' . now()->format('Y_m_d_His') . '.json';
        $filePath = $exportPath . '/' . $fileName;

        $jsonData = $employees->toJson(JSON_PRETTY_PRINT);

        File::put($filePath, $jsonData);

        $this->info("{$count} employees exported successfully.");
        $this->line("File saved at:");
        $this->line($filePath);

        return Command::SUCCESS;
    }

}
