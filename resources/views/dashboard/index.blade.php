@extends('layouts.app')

@section('title', 'Dashboard — MGVCL Feeder')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0 fw-bold">Dashboard</h4>
        <small class="text-muted">Live feeder power position</small>
    </div>
    <div class="d-flex align-items-center gap-2">
        <span class="text-muted small" id="lastRefreshed">
            <i class="bi bi-arrow-repeat me-1"></i> Live
        </span>
        @can('export-report')
        <a href="{{ route('reports.export') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-download me-1"></i> Export CSV
        </a>
        @endcan
    </div>
</div>

{{-- Summary Cards --}}
<div class="row g-3 mb-4" id="summaryCards">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center h-100">
            <div class="card-body py-3">
                <div class="fs-2 fw-bold text-primary" id="cardTotal">{{ $summary['total'] }}</div>
                <div class="text-muted small">Total Feeders</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center h-100" style="border-left: 4px solid #198754 !important;">
            <div class="card-body py-3">
                <div class="fs-2 fw-bold text-success" id="cardOn">{{ $summary['fullyOn'] }}</div>
                <div class="text-muted small"><i class="bi bi-circle-fill text-success me-1" style="font-size:.6rem;"></i>Fully ON</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center h-100" style="border-left: 4px solid #fd7e14 !important;">
            <div class="card-body py-3">
                <div class="fs-2 fw-bold text-warning" id="cardPartial">{{ $summary['partialOn'] }}</div>
                <div class="text-muted small"><i class="bi bi-circle-fill text-warning me-1" style="font-size:.6rem;"></i>Partially ON</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center h-100" style="border-left: 4px solid #dc3545 !important;">
            <div class="card-body py-3">
                <div class="fs-2 fw-bold text-danger" id="cardOff">{{ $summary['fullyOff'] }}</div>
                <div class="text-muted small"><i class="bi bi-circle-fill text-danger me-1" style="font-size:.6rem;"></i>Fully OFF</div>
            </div>
        </div>
    </div>
</div>

{{-- Division Breakdown --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white fw-semibold py-3">
        <i class="bi bi-bar-chart me-2"></i>Division-wise Status
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Division</th>
                        <th class="text-success text-center">Fully ON</th>
                        <th class="text-warning text-center">Partial ON</th>
                        <th class="text-danger text-center">Fully OFF</th>
                        <th class="text-center">Total</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($divisions as $division)
                    <tr data-division-id="{{ $division->id }}">
                        <td class="ps-3 fw-semibold">{{ $division->name }}</td>
                        <td class="text-center">
                            <span class="badge badge-fully-on" data-div-status="fully_on">{{ $division->feeders_on ?? 0 }}</span>
                        </td>
                        <td class="text-center">
                            <span class="badge badge-partially-on" data-div-status="partially_on">{{ $division->feeders_partial ?? 0 }}</span>
                        </td>
                        <td class="text-center">
                            <span class="badge badge-fully-off" data-div-status="fully_off">{{ $division->feeders_off ?? 0 }}</span>
                        </td>
                        <td class="text-center text-muted" data-div-status="total">{{ $division->total_feeders ?? 0 }}</td>
                        <td>
                            <a href="{{ route('feeders.index', ['division_id' => $division->id]) }}"
                               class="btn btn-outline-primary btn-sm">
                                View <i class="bi bi-arrow-right"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">No divisions found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Sub-Division Breakdown --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white fw-semibold py-3">
        <i class="bi bi-diagram-2 me-2"></i>Sub-Division-wise Status
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Sub-Division</th>
                        <th class="text-muted">Division</th>
                        <th class="text-success text-center">Fully ON</th>
                        <th class="text-warning text-center">Partial ON</th>
                        <th class="text-danger text-center">Fully OFF</th>
                        <th class="text-center">Total</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($subDivisions as $sd)
                    <tr data-sub-division-id="{{ $sd->id }}">
                        <td class="ps-3 fw-semibold">{{ $sd->name }}</td>
                        <td class="text-muted small">{{ $sd->division_name }}</td>
                        <td class="text-center">
                            <span class="badge badge-fully-on" data-subdiv-status="fully_on">{{ $sd->feeders_on ?? 0 }}</span>
                        </td>
                        <td class="text-center">
                            <span class="badge badge-partially-on" data-subdiv-status="partially_on">{{ $sd->feeders_partial ?? 0 }}</span>
                        </td>
                        <td class="text-center">
                            <span class="badge badge-fully-off" data-subdiv-status="fully_off">{{ $sd->feeders_off ?? 0 }}</span>
                        </td>
                        <td class="text-center text-muted" data-subdiv-status="total">{{ $sd->total_feeders ?? 0 }}</td>
                        <td>
                            <a href="{{ route('feeders.index', ['sub_division_id' => $sd->id]) }}"
                               class="btn btn-outline-primary btn-sm">
                                View <i class="bi bi-arrow-right"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">No sub-divisions found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Map status key → summary card element id
    const cardMap = {
        fully_on:     'cardOn',
        partially_on: 'cardPartial',
        fully_off:    'cardOff',
    };

    function adjustCard(status, delta) {
        const el = document.getElementById(cardMap[status]);
        if (el) el.textContent = Math.max(0, parseInt(el.textContent) + delta);
    }

    function adjustDivisionRow(divisionId, oldStatus, newStatus) {
        const row = document.querySelector(`tr[data-division-id="${divisionId}"]`);
        if (!row) return;

        const oldBadge = row.querySelector(`[data-div-status="${oldStatus}"]`);
        const newBadge = row.querySelector(`[data-div-status="${newStatus}"]`);

        if (oldBadge) oldBadge.textContent = Math.max(0, parseInt(oldBadge.textContent) - 1);
        if (newBadge) newBadge.textContent = parseInt(newBadge.textContent) + 1;
    }

    function adjustSubDivisionRow(subDivisionId, oldStatus, newStatus) {
        const row = document.querySelector(`tr[data-sub-division-id="${subDivisionId}"]`);
        if (!row) return;

        const oldBadge = row.querySelector(`[data-subdiv-status="${oldStatus}"]`);
        const newBadge = row.querySelector(`[data-subdiv-status="${newStatus}"]`);

        if (oldBadge) oldBadge.textContent = Math.max(0, parseInt(oldBadge.textContent) - 1);
        if (newBadge) newBadge.textContent = parseInt(newBadge.textContent) + 1;
    }

    function markUpdated() {
        document.getElementById('lastRefreshed').innerHTML =
            `<i class="bi bi-arrow-repeat me-1"></i> Updated ${new Date().toLocaleTimeString()}`;
    }

    window.Echo.channel('feeders').listen('FeederStatusUpdated', function (data) {
        // Update summary cards
        if (data.old_status !== data.new_status) {
            adjustCard(data.old_status, -1);
            adjustCard(data.new_status, +1);
        }

        // Update division breakdown row
        if (data.division_id && data.old_status !== data.new_status) {
            adjustDivisionRow(data.division_id, data.old_status, data.new_status);
        }

        // Update sub-division breakdown row
        if (data.sub_division_id && data.old_status !== data.new_status) {
            adjustSubDivisionRow(data.sub_division_id, data.old_status, data.new_status);
        }

        markUpdated();
    });
</script>
@endpush
