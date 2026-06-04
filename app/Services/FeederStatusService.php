<?php

namespace App\Services;

use App\Models\Feeder;
use App\Models\FeederStatusLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class FeederStatusService
{
    public function updateStatus(Feeder $feeder, string $newStatus, ?string $remarks, User $updatedBy): void
    {
        $oldStatus = $feeder->current_status;

        DB::transaction(function () use ($feeder, $newStatus, $oldStatus, $remarks, $updatedBy) {
            $feeder->update([
                'current_status'  => $newStatus,
                'last_updated_by' => $updatedBy->id,
                'last_updated_at' => now(),
            ]);

            FeederStatusLog::create([
                'feeder_id'  => $feeder->id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'remarks'    => $remarks,
                'updated_by' => $updatedBy->id,
            ]);
        });
    }
}
