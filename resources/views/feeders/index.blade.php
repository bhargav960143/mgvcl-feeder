@extends('layouts.app')

@section('title', 'Feeder Status — MGVCL')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-0 fw-bold">Feeder Status</h4>
        <small class="text-muted">{{ count($feeders) }} feeders found</small>
    </div>
    @can('export-report')
    <div class="dropdown">
        <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
            <i class="bi bi-download me-1"></i> Export
        </button>
        <ul class="dropdown-menu dropdown-menu-end">
            <li>
                <a class="dropdown-item" href="{{ route('reports.export') }}">
                    <i class="bi bi-filetype-csv me-2 text-secondary"></i> CSV
                </a>
            </li>
            <li>
                <a class="dropdown-item" href="#" id="exportExcel">
                    <i class="bi bi-file-earmark-excel me-2 text-success"></i> Excel (.xlsx)
                </a>
            </li>
            <li>
                <a class="dropdown-item" href="#" id="exportPdf">
                    <i class="bi bi-file-earmark-pdf me-2 text-danger"></i> PDF
                </a>
            </li>
            <li><hr class="dropdown-divider"></li>
            <li>
                <a class="dropdown-item" href="#" id="exportWhatsapp">
                    <i class="bi bi-whatsapp me-2 text-success"></i> WhatsApp (Copy)
                </a>
            </li>
        </ul>
    </div>
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

            {{-- Division filter --}}
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

{{-- Bulk Action Toolbar --}}
<div id="bulkToolbar" class="card border-0 shadow-sm mb-3 bg-primary text-white d-none">
    <div class="card-body py-2 px-3 d-flex align-items-center gap-3">
        <span id="bulkCount" class="fw-semibold">0 selected</span>
        <button type="button" class="btn btn-light btn-sm fw-semibold" data-bs-toggle="modal" data-bs-target="#bulkUpdateModal">
            <i class="bi bi-pencil-square me-1"></i> Bulk Update Status
        </button>
        <button type="button" class="btn btn-outline-light btn-sm" id="clearSelection">
            <i class="bi bi-x-circle me-1"></i> Clear Selection
        </button>
    </div>
</div>

{{-- Feeder Table --}}
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0" id="feedersTable">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3" style="width:40px;">
                            <input type="checkbox" id="selectAll" title="Select all visible">
                        </th>
                        <th>#</th>
                        <th>Feeder Name</th>
                        <th class="text-center">Quick Status</th>
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
                    <tr data-feeder-row="{{ $feeder->id }}">
                        <td class="ps-3">
                            @can('updateStatus', $feeder)
                            <input type="checkbox" class="feeder-select" value="{{ $feeder->id }}">
                            @endcan
                        </td>
                        <td class="text-muted small">{{ $loop->iteration }}</td>
                        <td class="fw-semibold">{{ $feeder->name }}</td>
                        <td class="text-center">
                            @can('update-feeder-status')
                            <div class="d-flex gap-1 justify-content-center">
                                <button type="button"
                                    class="btn btn-xs quick-status-btn {{ $feeder->current_status === 'fully_on' ? 'btn-success' : 'btn-outline-success' }}"
                                    data-feeder-id="{{ $feeder->id }}"
                                    data-status="fully_on"
                                    {{ $feeder->current_status === 'fully_on' ? 'disabled' : '' }}
                                    title="Mark Fully ON">ON</button>
                                <button type="button"
                                    class="btn btn-xs quick-status-btn {{ $feeder->current_status === 'partially_on' ? 'btn-warning' : 'btn-outline-warning' }}"
                                    data-feeder-id="{{ $feeder->id }}"
                                    data-status="partially_on"
                                    {{ $feeder->current_status === 'partially_on' ? 'disabled' : '' }}
                                    title="Mark Partially ON">~ON</button>
                                <button type="button"
                                    class="btn btn-xs quick-status-btn {{ $feeder->current_status === 'fully_off' ? 'btn-danger' : 'btn-outline-danger' }}"
                                    data-feeder-id="{{ $feeder->id }}"
                                    data-status="fully_off"
                                    {{ $feeder->current_status === 'fully_off' ? 'disabled' : '' }}
                                    title="Mark Fully OFF">OFF</button>
                            </div>
                            @else
                            @include('feeders.partials.status-badge', ['status' => $feeder->current_status])
                            @endcan
                        </td>
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
                        <td colspan="13" class="text-center py-5 text-muted">
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

{{-- Update Status Modal (single feeder) --}}
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

{{-- Bulk Update Status Modal --}}
<div class="modal fade" id="bulkUpdateModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Bulk Update Feeder Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="bulkUpdateForm" method="POST" action="{{ route('feeders.bulkUpdateStatus') }}">
                @csrf
                @method('PATCH')
                <div id="bulkFeederIds"></div>
                <div class="modal-body">
                    <p class="text-muted mb-3">
                        Updating <strong id="bulkModalCount">0</strong> feeder(s).
                    </p>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">New Status <span class="text-danger">*</span></label>
                        <div class="d-flex gap-2 flex-wrap">
                            @foreach(['fully_on' => ['Fully ON','success'], 'partially_on' => ['Partially ON','warning'], 'fully_off' => ['Fully OFF','danger']] as $val => [$label, $color])
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio"
                                       name="status" id="bulk_status_{{ $val }}" value="{{ $val }}">
                                <label class="form-check-label fw-semibold text-{{ $color }}" for="bulk_status_{{ $val }}">
                                    {{ $label }}
                                </label>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="mb-2">
                        <label for="bulk_remarks" class="form-label fw-semibold">Remarks</label>
                        <textarea name="remarks" id="bulk_remarks" class="form-control" rows="3"
                                  placeholder="Optional — reason for status change..."></textarea>
                        <div class="form-text">Required when marking as OFF or PARTIAL.</div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle me-1"></i> Update All Selected
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
{{-- WhatsApp Copy Modal --}}
<div class="modal fade" id="whatsappModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold"><i class="bi bi-whatsapp text-success me-2"></i>WhatsApp Message</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small mb-2">Copy and paste this into WhatsApp:</p>
                <textarea id="whatsappText" class="form-control font-monospace" rows="18" readonly style="font-size:.8rem;resize:none;"></textarea>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-success" id="copyWhatsapp">
                    <i class="bi bi-clipboard me-1"></i> Copy to Clipboard
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Toast for copy feedback --}}
<div class="position-fixed bottom-0 end-0 p-3" style="z-index:9999">
    <div id="copyToast" class="toast align-items-center text-bg-success border-0" role="alert">
        <div class="d-flex">
            <div class="toast-body"><i class="bi bi-check-circle me-1"></i> Copied to clipboard!</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .btn-xs { padding: .15rem .4rem; font-size: .72rem; line-height: 1.4; border-radius: .2rem; }
</style>
@endpush

@push('scripts')
<script>
    // --- DataTables init ---
    const table = $('#feedersTable').DataTable({
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100, 200, -1], [10, 25, 50, 100, 200, 'All']],
        order: [],
        searching: true,
        columnDefs: [{ orderable: false, targets: [0, 3] }],
        language: { search: 'Quick search:', zeroRecords: 'No feeders found.' }
    });

    // --- Bulk selection state (persists across DataTables pages) ---
    const selectedIds = new Set();

    function updateBulkToolbar() {
        const count = selectedIds.size;
        document.getElementById('bulkToolbar').classList.toggle('d-none', count === 0);
        document.getElementById('bulkCount').textContent = count + ' selected';
    }

    function syncSelectAll() {
        const visible = $('.feeder-select');
        const checkedVisible = visible.filter(':checked').length;
        const selectAll = document.getElementById('selectAll');
        selectAll.indeterminate = checkedVisible > 0 && checkedVisible < visible.length;
        selectAll.checked = visible.length > 0 && checkedVisible === visible.length;
    }

    // Restore checkboxes after DataTables redraws (page change / search)
    table.on('draw', function () {
        $('.feeder-select').each(function () {
            this.checked = selectedIds.has(this.value);
        });
        syncSelectAll();
        updateBulkToolbar();
    });

    // Individual checkbox change
    $(document).on('change', '.feeder-select', function () {
        if (this.checked) selectedIds.add(this.value);
        else selectedIds.delete(this.value);
        syncSelectAll();
        updateBulkToolbar();
    });

    // Select all visible
    document.getElementById('selectAll').addEventListener('change', function () {
        $('.feeder-select').each(function () {
            this.checked = document.getElementById('selectAll').checked;
            if (this.checked) selectedIds.add(this.value);
            else selectedIds.delete(this.value);
        });
        updateBulkToolbar();
    });

    // Clear selection
    document.getElementById('clearSelection').addEventListener('click', function () {
        selectedIds.clear();
        $('.feeder-select').prop('checked', false);
        document.getElementById('selectAll').checked = false;
        document.getElementById('selectAll').indeterminate = false;
        updateBulkToolbar();
    });

    // Populate bulk modal with selected IDs
    document.getElementById('bulkUpdateModal').addEventListener('show.bs.modal', function () {
        const container = document.getElementById('bulkFeederIds');
        container.innerHTML = '';
        selectedIds.forEach(function (id) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'feeder_ids[]';
            input.value = id;
            container.appendChild(input);
        });
        document.getElementById('bulkModalCount').textContent = selectedIds.size;
        document.getElementById('bulk_remarks').value = '';
        const radios = document.querySelectorAll('#bulkUpdateForm input[name="status"]');
        radios.forEach(r => r.checked = false);
    });

    // --- Visibility reload ---
    let hiddenAt = null;
    document.addEventListener('visibilitychange', function () {
        if (document.visibilityState === 'hidden') {
            hiddenAt = Date.now();
        } else if (document.visibilityState === 'visible' && hiddenAt !== null) {
            if (Date.now() - hiddenAt >= 15000) {
                location.reload();
            }
            hiddenAt = null;
        }
    });

    // --- Live status updates via WebSocket ---
    const statusMap = {
        fully_on:     { cls: 'badge-fully-on',     label: 'Fully ON'     },
        partially_on: { cls: 'badge-partially-on', label: 'Partially ON' },
        fully_off:    { cls: 'badge-fully-off',     label: 'Fully OFF'    },
    };

    function applyRowStatus(row, newStatus) {
        const s = statusMap[newStatus] ?? { cls: 'bg-secondary', label: newStatus };

        const statusCell = row.querySelector('.feeder-status-cell');
        if (statusCell) {
            statusCell.innerHTML = `<span class="badge ${s.cls}" style="font-size:.8rem;padding:.35em .65em;">${s.label}</span>`;
        }

        // Update modal button's stored status
        const modalBtn = row.querySelector('[data-bs-target="#updateModal"]');
        if (modalBtn) modalBtn.dataset.feederStatus = newStatus;

        // Update quick-status buttons: active=filled+disabled, others=outline+enabled
        row.querySelectorAll('.quick-status-btn').forEach(function (b) {
            const isActive = b.dataset.status === newStatus;
            const color = b.dataset.status === 'fully_on' ? 'success'
                        : b.dataset.status === 'partially_on' ? 'warning' : 'danger';
            b.className = `btn btn-xs quick-status-btn ${isActive ? 'btn-' + color : 'btn-outline-' + color}`;
            b.disabled = isActive;
        });
    }

    window.Echo.channel('feeders').listen('FeederStatusUpdated', function (data) {
        const row = document.querySelector(`tr[data-feeder-row="${data.feeder_id}"]`);
        if (!row) return;

        applyRowStatus(row, data.new_status);

        const timeCell = row.querySelector('.feeder-time-cell');
        if (timeCell) {
            timeCell.innerHTML = `<span>${data.last_updated_at}</span><br><span class="text-muted" style="font-size:.75rem;">${data.updated_by}</span>`;
        }
    });

    // --- Quick status inline change ---
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    $(document).on('click', '.quick-status-btn', function () {
        const btn      = this;
        const feederId = btn.dataset.feederId;
        const status   = btn.dataset.status;
        const row      = btn.closest('tr');

        btn.disabled = true;

        fetch(`/feeders/${feederId}/status`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'Accept':       'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({ status }),
        })
        .then(function (res) {
            if (!res.ok) throw new Error('Server error ' + res.status);
            applyRowStatus(row, status);
        })
        .catch(function () {
            btn.disabled = false;
            alert('Status update failed. Please try again.');
        });
    });

    // --- Single feeder modal ---
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

    // --- Export helpers ---

    // Extract all filtered rows from DataTables (all pages, not just visible)
    function getExportRows() {
        const rows = [];
        table.rows({ search: 'applied' }).every(function () {
            const cells = this.node().querySelectorAll('td');
            rows.push({
                sr:          cells[1].textContent.trim(),
                name:        cells[2].textContent.trim(),
                tnd:         cells[4].textContent.trim(),
                division:    cells[5].textContent.trim(),
                subDivision: cells[6].textContent.trim(),
                substation:  cells[7].textContent.trim(),
                category:    cells[8].textContent.trim(),
                consumers:   cells[9].textContent.trim(),
                status:      cells[10].textContent.trim(),
                lastUpdated: cells[11].textContent.trim(),
            });
        });
        return rows;
    }

    // --- Excel export (SheetJS) ---
    document.getElementById('exportExcel').addEventListener('click', function (e) {
        e.preventDefault();
        const rows = getExportRows();
        const headers = ['SR NO', 'Feeder Name', 'TND Code', 'Division', 'Sub Division', 'Substation', 'Category', 'Consumers', 'Status', 'Last Updated'];
        const data = [headers, ...rows.map(r => [r.sr, r.name, r.tnd, r.division, r.subDivision, r.substation, r.category, r.consumers, r.status, r.lastUpdated])];

        const ws = XLSX.utils.aoa_to_sheet(data);

        // Column widths
        ws['!cols'] = [5, 28, 14, 18, 18, 18, 10, 12, 14, 20].map(w => ({ wch: w }));

        // Header row style (bold)
        headers.forEach((_, i) => {
            const cell = ws[XLSX.utils.encode_cell({ r: 0, c: i })];
            if (cell) cell.s = { font: { bold: true } };
        });

        const wb = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(wb, ws, 'Feeder Status');

        const now = new Date();
        const stamp = now.getFullYear() + '-' + String(now.getMonth()+1).padStart(2,'0') + '-' + String(now.getDate()).padStart(2,'0');
        XLSX.writeFile(wb, `feeder-status-${stamp}.xlsx`);
    });

    // --- PDF export (jsPDF + AutoTable) ---
    document.getElementById('exportPdf').addEventListener('click', function (e) {
        e.preventDefault();
        const rows = getExportRows();
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF({ orientation: 'landscape', unit: 'pt', format: 'a4' });

        doc.setFontSize(14);
        doc.text('MGVCL Feeder Status Report', 40, 40);
        doc.setFontSize(9);
        doc.setTextColor(120);
        const now = new Date();
        doc.text('Generated: ' + now.toLocaleDateString('en-IN', { day:'2-digit', month:'short', year:'numeric', hour:'2-digit', minute:'2-digit' }), 40, 56);
        doc.text('Total feeders: ' + rows.length, 40, 68);
        doc.setTextColor(0);

        doc.autoTable({
            startY: 80,
            head: [['#', 'Feeder Name', 'TND Code', 'Division', 'Sub Div', 'Substation', 'Cat', 'Consumers', 'Status', 'Last Updated']],
            body: rows.map(r => [r.sr, r.name, r.tnd, r.division, r.subDivision, r.substation, r.category, r.consumers, r.status, r.lastUpdated]),
            styles: { fontSize: 7, cellPadding: 3 },
            headStyles: { fillColor: [13, 110, 253], textColor: 255, fontStyle: 'bold' },
            alternateRowStyles: { fillColor: [245, 247, 250] },
            columnStyles: {
                0: { cellWidth: 25 },
                1: { cellWidth: 90 },
                2: { cellWidth: 65 },
                3: { cellWidth: 70 },
                4: { cellWidth: 70 },
                5: { cellWidth: 70 },
                6: { cellWidth: 35 },
                7: { cellWidth: 55 },
                8: { cellWidth: 55 },
                9: { cellWidth: 80 },
            },
            didDrawCell: function (data) {
                if (data.section === 'body' && data.column.index === 8) {
                    const status = data.cell.raw;
                    if (status === 'Fully ON')     doc.setTextColor(25, 135, 84);
                    else if (status === 'Fully OFF')    doc.setTextColor(220, 53, 69);
                    else if (status === 'Partially ON') doc.setTextColor(255, 193, 7);
                }
            },
        });

        const stamp = now.getFullYear() + '-' + String(now.getMonth()+1).padStart(2,'0') + '-' + String(now.getDate()).padStart(2,'0');
        doc.save(`feeder-status-${stamp}.pdf`);
    });

    // --- WhatsApp export ---
    document.getElementById('exportWhatsapp').addEventListener('click', function (e) {
        e.preventDefault();
        const rows = getExportRows();

        const groups = { 'Fully ON': [], 'Partially ON': [], 'Fully OFF': [] };
        rows.forEach(r => {
            if (groups[r.status] !== undefined) groups[r.status].push(r);
            else groups[r.status] = [r];
        });

        const statusEmoji = { 'Fully ON': '✅', 'Partially ON': '⚠️', 'Fully OFF': '❌' };
        const now = new Date();
        const dateStr = now.toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' });
        const timeStr = now.toLocaleTimeString('en-IN', { hour: '2-digit', minute: '2-digit' });

        let msg = `📊 *MGVCL Feeder Status Report*\n`;
        msg += `📅 Date: ${dateStr} ${timeStr}\n`;
        msg += `📌 Total Feeders: ${rows.length}\n\n`;

        Object.entries(groups).forEach(([status, feeders]) => {
            if (!feeders.length) return;
            const emoji = statusEmoji[status] || '🔵';
            msg += `${emoji} *${status}* (${feeders.length})\n`;
            feeders.forEach(r => {
                msg += `• ${r.subDivision} — ${r.name}\n`;
            });
            msg += '\n';
        });

        msg += `_Exported from MGVCL Portal_`;

        document.getElementById('whatsappText').value = msg;
        new bootstrap.Modal(document.getElementById('whatsappModal')).show();
    });

    // Copy to clipboard
    document.getElementById('copyWhatsapp').addEventListener('click', function () {
        const text = document.getElementById('whatsappText').value;
        navigator.clipboard.writeText(text).then(function () {
            new bootstrap.Toast(document.getElementById('copyToast')).show();
        });
    });
</script>

<script src="https://cdn.sheetjs.com/xlsx-0.20.3/package/dist/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>
@endpush
