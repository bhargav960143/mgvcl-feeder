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

            $tndCode     = $this->sanitize($tndCode);
            $feederName  = $this->sanitize($feederName);
            $divisionName    = $this->sanitize($divisionName);
            $subDivisionName = $this->sanitize($subDivisionName);
            $ssName      = $this->sanitize($ssName);
            $category    = $this->sanitize($category);

            if (empty($tndCode) || empty($feederName)) {
                $skipped++;
                continue;
            }

            $division = Division::firstOrCreate(
                ['circle_id' => $circle->id, 'name' => $divisionName]
            );

            $subDivision = SubDivision::firstOrCreate(
                ['division_id' => $division->id, 'name' => $subDivisionName]
            );

            $substation = Substation::firstOrCreate(
                ['sub_division_id' => $subDivision->id, 'name' => $ssName]
            );

            $exists = Feeder::where('tnd_code', $tndCode)->exists();

            Feeder::updateOrCreate(
                ['tnd_code' => $tndCode],
                [
                    'substation_id'  => $substation->id,
                    'name'           => $feederName,
                    'category'       => $category,
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

    private function sanitize(?string $value): string
    {
        if ($value === null) return '';
        // Strip invalid UTF-8 bytes (e.g. non-breaking space \xA0 from Excel CSV exports)
        $clean = iconv('UTF-8', 'UTF-8//IGNORE', $value);
        return trim($clean === false ? '' : $clean);
    }
}
