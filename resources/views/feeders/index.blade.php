@extends('layouts.app')

@section('title', 'Feeder Status — MGVCL')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-0 fw-bold">Feeder Status</h4>
        <small class="text-muted">{{ count($feeders) }} feeders found</small>
    </div>
    @can('export-report')
    <a href="{{ route('reports.export') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-download me-1"></i> Export CSV
    </a>
    @endcan
</div>

{{-- Filters --}}
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2">
        <form method="GET" action="{{ route('feeders.index') }}" class="row g-2 align-items-end">

            {{-- Search --}}
            <div class="col-12 col-md-3">
                <input type="text" name="search" class="form-control form-control-sm"
                       placeholder="Search feeder / TND code"
                       value="{{ request('search') }}">
            </div>

            {{-- Status --}}
            <div class="col-6 col-md-2">
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Status</option>
                    @foreach($statusLabels as $val => $label)
                    <option value="{{ $val }}" {{ request('status') == $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Category --}}
            <div class="col-6 col-md-2">
                <select name="category" class="form-select form-select-sm">
                    <option value="">All Category</option>
                    @foreach($categories as $cat)
                    <option value="{{ $cat }}" {{ request('category') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Division filter — admin/circle only --}}
            @if($divisions->isNotEmpty())
            <div class="col-6 col-md-2">
                <select name="division_id" class="form-select form-select-sm">
                    <option value="">All Divisions</option>
                    @foreach($divisions as $division)
                    <option value="{{ $division->id }}" {{ request('division_id') == $division->id ? 'selected' : '' }}>
                        {{ $division->name }}
                    </option>
                    @endforeach
                </select>
            </div>
            @endif

            {{-- Sub Division filter --}}
            @if($subDivisions->isNotEmpty())
            <div class="col-6 col-md-2">
                <select name="sub_division_id" class="form-select form-select-sm">
                    <option value="">All Sub Divs</option>
                    @foreach($subDivisions as $sd)
                    <option value="{{ $sd->id }}" {{ request('sub_division_id') == $sd->id ? 'selected' : '' }}>
                        {{ $sd->name }}
                    </option>
                    @endforeach
                </select>
            </div>
            @endif

            <div class="col-auto d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="bi bi-search me-1"></i> Filter
                </button>
                <a href="{{ route('feeders.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-x-circle"></i> Clear
                </a>
            </div>
        </form>
    </div>
</div>

{{-- Feeder Table --}}
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0" id="feedersTable">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">#</th>
                        <th>Feeder Name</th>
                        <th>TND Code</th>
                        <th>Division</th>
                        <th>Sub Division</th>
                        <th>Substation</th>
                        <th>Category</th>
                        <th class="text-center">Consumers</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Last Updated</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($feeders as $feeder)
                    <tr>
                        <td class="ps-3 text-muted small">{{ $loop->iteration }}</td>
                        <td class="fw-semibold">{{ $feeder->name }}</td>
                        <td><code class="text-secondary">{{ $feeder->tnd_code }}</code></td>
                        <td class="small">{{ $feeder->substation->subDivision->division->name ?? '—' }}</td>
                        <td class="small">{{ $feeder->substation->subDivision->name ?? '—' }}</td>
                        <td class="small">{{ $feeder->substation->name ?? '—' }}</td>
                        <td><span class="badge bg-light text-dark border">{{ $feeder->category }}</span></td>
                        <td class="text-center small">{{ number_format($feeder->total_consumer) }}</td>
                        <td class="text-center feeder-status-cell">
                            @include('feeders.partials.status-badge', ['status' => $feeder->current_status])
                        </td>
                        <td class="text-center small text-muted feeder-time-cell">
                            @if($feeder->last_updated_at)
                                <span title="{{ $feeder->last_updated_at->format('d-M-Y H:i') }}">
                                    {{ $feeder->last_updated_at->diffForHumans() }}
                                </span>
                                <br>
                                <span class="text-muted" style="font-size:.75rem;">
                                    {{ $feeder->lastUpdatedBy?->name ?? '—' }}
                                </span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="d-flex gap-1 justify-content-center">
                                @can('updateStatus', $feeder)
                                <button type="button"
                                        class="btn btn-sm btn-outline-primary"
                                        data-bs-toggle="modal"
                                        data-bs-target="#updateModal"
                                        data-feeder-id="{{ $feeder->id }}"
                                        data-feeder-name="{{ $feeder->name }}"
                                        data-feeder-status="{{ $feeder->current_status }}">
                                    <i class="bi bi-pencil-square"></i> Update
                                </button>
                                @endcan
                                @can('view-status-logs')
                                <a href="{{ route('feeders.logs', $feeder) }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-clock-history"></i>
                                </a>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="11" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                            No feeders found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

{{-- Update Status Modal --}}
<div class="modal fade" id="updateModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Update Feeder Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="updateStatusForm" method="POST" action="">
                @csrf
                @method('PATCH')
                <div class="modal-body">
                    <p class="text-muted mb-3">
                        Feeder: <strong id="modalFeederName"></strong>
                    </p>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">New Status <span class="text-danger">*</span></label>
                        <div class="d-flex gap-2 flex-wrap">
                            @foreach(['fully_on' => ['Fully ON','success'], 'partially_on' => ['Partially ON','warning'], 'fully_off' => ['Fully OFF','danger']] as $val => [$label, $color])
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio"
                                       name="status" id="status_{{ $val }}" value="{{ $val }}">
                                <label class="form-check-label fw-semibold text-{{ $color }}" for="status_{{ $val }}">
                                    {{ $label }}
                                </label>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="mb-2">
                        <label for="remarks" class="form-label fw-semibold">Remarks</label>
                        <textarea name="remarks" id="remarks" class="form-control" rows="3"
                                  placeholder="Optional — reason for status change..."></textarea>
                        <div class="form-text">Required when marking as OFF or PARTIAL.</div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle me-1"></i> Update Status
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Override DataTables for feeder table — disable built-in search (server-side filters handle it)
    $(function () {
        $('#feedersTable').DataTable({
            pageLength: 25,
            lengthMenu: [10, 25, 50, 100, 200],
            order: [],
            searching: true,
            language: { search: 'Quick search:', zeroRecords: 'No feeders found.' }
        });
    });

    // Reload when tab regains focus — ensures stale status data is never shown
    let hiddenAt = null;
    document.addEventListener('visibilitychange', function () {
        if (document.visibilityState === 'hidden') {
            hiddenAt = Date.now();
        } else if (document.visibilityState === 'visible' && hiddenAt !== null) {
            // Only reload if tab was hidden for at least 15 seconds
            if (Date.now() - hiddenAt >= 15000) {
                location.reload();
            }
            hiddenAt = null;
        }
    });

    // Live status updates via Reverb WebSocket
    const statusMap = {
        fully_on:     { cls: 'badge-fully-on',     label: 'Fully ON'     },
        partially_on: { cls: 'badge-partially-on', label: 'Partially ON' },
        fully_off:    { cls: 'badge-fully-off',     label: 'Fully OFF'    },
    };

    window.Echo.channel('feeders').listen('FeederStatusUpdated', function (data) {
        const btn = document.querySelector(`[data-feeder-id="${data.feeder_id}"]`);
        if (!btn) return;

        const row = btn.closest('tr');
        const s   = statusMap[data.new_status] ?? { cls: 'bg-secondary', label: data.new_status };

        // Update status badge cell
        const statusCell = row.querySelector('.feeder-status-cell');
        if (statusCell) {
            statusCell.innerHTML = `<span class="badge ${s.cls}" style="font-size:.8rem;padding:.35em .65em;">${s.label}</span>`;
        }

        // Update last-updated cell
        const timeCell = row.querySelector('.feeder-time-cell');
        if (timeCell) {
            timeCell.innerHTML = `<span>${data.last_updated_at}</span><br><span class="text-muted" style="font-size:.75rem;">${data.updated_by}</span>`;
        }

        // Keep button data-attribute in sync for modal
        btn.dataset.feederStatus = data.new_status;
    });

    // show.bs.modal fires after Bootstrap resolves relatedTarget — safe with DataTables re-renders
    document.getElementById('updateModal').addEventListener('show.bs.modal', function (event) {
        const btn    = event.relatedTarget;
        const id     = btn.dataset.feederId;
        const name   = btn.dataset.feederName;
        const status = btn.dataset.feederStatus;

        document.getElementById('modalFeederName').textContent = name;
        document.getElementById('updateStatusForm').action = `/feeders/${id}/status`;

        const radio = document.getElementById('status_' + status);
        if (radio) radio.checked = true;

        document.getElementById('remarks').value = '';
    });
</script>
@endpush
