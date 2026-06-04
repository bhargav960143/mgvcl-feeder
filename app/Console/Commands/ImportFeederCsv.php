<?php

namespace App\Console\Commands;

use App\Models\Circle;
use App\Models\Division;
use App\Models\Feeder;
use App\Models\SubDivision;
use App\Models\Substation;
use Illuminate\Console\Command;

class ImportFeederCsv extends Command
{
    protected $signature = 'feeder:import {file : Path to CSV file} {--circle= : Circle name to assign all divisions to}';

    protected $description = 'Import feeder data from CSV. Circle must exist first.';

    public function handle(): int
    {
        $file = $this->argument('file');
        $circleName = $this->option('circle');

        if (! file_exists($file)) {
            $this->error("File not found: {$file}");
            return self::FAILURE;
        }

        $circle = Circle::where('name', $circleName)->first();

        if (! $circle) {
            $this->error("Circle '{$circleName}' not found. Create it first via admin panel or tinker.");
            return self::FAILURE;
        }

        $handle = fopen($file, 'r');
        $header = fgetcsv($handle); // skip header row

        $created = $updated = $skipped = 0;

        while (($row = fgetcsv($handle)) !== false) {
            // Skip empty rows
            if (empty(array_filter($row))) {
                continue;
            }

            [
                $srNo, $divisionName, $subDivisionName, $ssName,
                $feederName, $category, $tndCode, $totalConsumer, $totalTc
            ] = array_pad($row, 9, null);

            if (empty($tndCode) || empty($feederName)) {
                $skipped++;
                continue;
            }

            $division = Division::firstOrCreate(
                ['circle_id' => $circle->id, 'name' => trim($divisionName)]
            );

            $subDivision = SubDivision::firstOrCreate(
                ['division_id' => $division->id, 'name' => trim($subDivisionName)]
            );

            $substation = Substation::firstOrCreate(
                ['sub_division_id' => $subDivision->id, 'name' => trim($ssName)]
            );

            $exists = Feeder::where('tnd_code', trim($tndCode))->exists();

            Feeder::updateOrCreate(
                ['tnd_code' => trim($tndCode)],
                [
                    'substation_id'  => $substation->id,
                    'name'           => trim($feederName),
                    'category'       => trim($category),
                    'total_consumer' => (int) $totalConsumer,
                    'total_tc'       => (int) $totalTc,
                    // current_status intentionally not overwritten on re-import
                ]
            );

            $exists ? $updated++ : $created++;
        }

        fclose($handle);

        $this->info("Import complete — Created: {$created} | Updated: {$updated} | Skipped: {$skipped}");

        return self::SUCCESS;
    }
}
