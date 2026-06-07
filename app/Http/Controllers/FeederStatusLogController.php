<?php

namespace App\Http\Controllers;

use App\Models\Feeder;
use App\Models\FeederStatusLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FeederStatusLogController extends Controller
{
    public function index(Request $request, Feeder $feeder): View
    {
        $this->authorizeFeederAccess($request->user(), $feeder);

        $logs = FeederStatusLog::with('updatedBy')
            ->where('feeder_id', $feeder->id)
            ->orderByDesc('created_at')
            ->paginate(30);

        return view('feeders.logs', compact('feeder', 'logs'));
    }

    private function authorizeFeederAccess($user, Feeder $feeder): void
    {
        if ($user->hasRole('admin')) return;

        if ($user->isCircleScoped()) {
            $feeder->load('substation.subDivision.division');
            if ($feeder->substation->subDivision->division->circle_id !== $user->jurisdiction_id) {
                abort(403);
            }
        }
    }
}
