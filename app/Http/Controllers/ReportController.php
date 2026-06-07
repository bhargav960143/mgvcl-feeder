<?php

namespace App\Http\Controllers;

use App\Models\Division;
use App\Models\Feeder;
use App\Models\SubDivision;
use App\Models\Substation;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function export(Request $request): StreamedResponse
    {
        $user  = $request->user();
        $query = Feeder::with(['substation.subDivision.division.circle', 'lastUpdatedBy'])->orderBy('name');

        if ($user->isCircleScoped()) {
            $query->whereIn('substation_id',
                Substation::whereIn(
                    'sub_division_id',
                    SubDivision::whereIn(
                        'division_id',
                        Division::where('circle_id', $user->jurisdiction_id)->pluck('id')
                    )->pluck('id')
                )->pluck('id')
            );
        }

        $filename = 'feeder-status-' . now()->format('Y-m-d-His') . '.csv';

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'SR NO', 'Circle', 'Division', 'Sub Division', 'Substation',
                'Feeder', 'TND Code', 'Category', 'Total Consumer', 'Total TC',
                'Status', 'Last Updated By', 'Last Updated At',
            ]);

            $query->chunk(200, function ($feeders) use ($handle) {
                foreach ($feeders as $i => $feeder) {
                    fputcsv($handle, [
                        $i + 1,
                        $feeder->substation->subDivision->division->circle->name ?? '',
                        $feeder->substation->subDivision->division->name ?? '',
                        $feeder->substation->subDivision->name ?? '',
                        $feeder->substation->name ?? '',
                        $feeder->name,
                        $feeder->tnd_code,
                        $feeder->category,
                        $feeder->total_consumer,
                        $feeder->total_tc,
                        $feeder->current_status,
                        $feeder->lastUpdatedBy?->name ?? '',
                        $feeder->last_updated_at?->format('d-M-Y H:i') ?? '',
                    ]);
                }
            });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }
}
