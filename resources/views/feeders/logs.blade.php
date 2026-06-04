@extends('layouts.app')
@section('title', 'Status Logs — ' . $feeder->name)
@section('content')
<div class="d-flex align-items-center mb-3">
    <a href="{{ route('feeders.index') }}" class="btn btn-sm btn-outline-secondary me-3"><i class="bi bi-arrow-left"></i></a>
    <div>
        <h4 class="mb-0 fw-bold">Status Logs</h4>
        <small class="text-muted">{{ $feeder->name }} — TND: {{ $feeder->tnd_code }}</small>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-3">#</th>
                    <th>Previous Status</th>
                    <th>New Status</th>
                    <th>Remarks</th>
                    <th>Updated By</th>
                    <th>Date & Time</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                <tr>
                    <td class="ps-3 text-muted small">{{ $logs->firstItem() + $loop->index }}</td>
                    <td>
                        @if($log->old_status)
                            @include('feeders.partials.status-badge', ['status' => $log->old_status])
                        @else
                            <span class="text-muted small">—</span>
                        @endif
                    </td>
                    <td>@include('feeders.partials.status-badge', ['status' => $log->new_status])</td>
                    <td class="small text-muted">{{ $log->remarks ?: '—' }}</td>
                    <td class="small">{{ $log->updatedBy?->name ?? '—' }}</td>
                    <td class="small text-muted">{{ $log->created_at->format('d-M-Y H:i:s') }}</td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center py-4 text-muted">No logs found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($logs->hasPages())<div class="card-footer bg-white">{{ $logs->links() }}</div>@endif
</div>
@endsection
