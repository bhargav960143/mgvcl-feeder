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

{{-- Status Breakdown Tabs --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-0 d-flex align-items-center justify-content-between">
        <ul class="nav nav-tabs card-header-tabs" id="statusTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active fw-semibold px-4" id="div-tab"
                        data-bs-toggle="tab" data-bs-target="#divTab"
                        type="button" role="tab">
                    <i class="bi bi-bar-chart me-1"></i> Div-wise
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-semibold px-4" id="subdiv-tab"
                        data-bs-toggle="tab" data-bs-target="#subDivTab"
                        type="button" role="tab">
                    <i class="bi bi-diagram-2 me-1"></i> Sub-Div-wise
                </button>
            </li>
        </ul>
        <div class="dropdown ms-3">
            <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                <i class="bi bi-download me-1"></i> Export
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="#" id="exportDivExcel"><i class="bi bi-file-earmark-excel me-2 text-success"></i>Excel</a></li>
                <li><a class="dropdown-item" href="#" id="exportDivPdf"><i class="bi bi-file-earmark-pdf me-2 text-danger"></i>PDF</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="#" id="exportDivWhatsapp"><i class="bi bi-whatsapp me-2 text-success"></i>WhatsApp (Copy)</a></li>
            </ul>
        </div>
    </div>
    <div class="tab-content">

        {{-- Division Tab --}}
        <div class="tab-pane fade show active" id="divTab" role="tabpanel">
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

        {{-- Sub-Division Tab --}}
        <div class="tab-pane fade" id="subDivTab" role="tabpanel">
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
</div>
{{-- WhatsApp Modal --}}
<div class="modal fade" id="waModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="bi bi-whatsapp text-success me-2"></i>WhatsApp Message</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <textarea id="waText" class="form-control font-monospace" rows="18" readonly style="font-size:.8rem;resize:none;"></textarea>
            </div>
            <div class="modal-footer">
                <button class="btn btn-success" id="copyWa"><i class="bi bi-clipboard me-1"></i> Copy</button>
                <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

{{-- Copy toast --}}
<div class="toast-container position-fixed bottom-0 end-0 p-3">
    <div id="waCopyToast" class="toast align-items-center text-bg-success border-0" role="alert">
        <div class="d-flex">
            <div class="toast-body"><i class="bi bi-check-circle me-1"></i> Copied to clipboard!</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
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

    // Persist active tab across page refreshes
    const tabEls = document.querySelectorAll('#statusTabs [data-bs-toggle="tab"]');
    const savedTab = localStorage.getItem('dashboard_tab');
    if (savedTab) {
        const el = document.querySelector(`#statusTabs [data-bs-target="${savedTab}"]`);
        if (el) bootstrap.Tab.getOrCreateInstance(el).show();
    }
    tabEls.forEach(el => el.addEventListener('shown.bs.tab', () => {
        localStorage.setItem('dashboard_tab', el.getAttribute('data-bs-target'));
    }));

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

    // --- Export helpers ---
    function getActiveTable() {
        const activePane = document.querySelector('.tab-pane.active');
        return activePane ? activePane.querySelector('table') : null;
    }

    function activeTabLabel() {
        const btn = document.querySelector('#statusTabs .nav-link.active');
        return btn ? btn.textContent.trim() : 'Status';
    }

    function tableToArray(table) {
        const rows = [];
        table.querySelectorAll('thead tr, tbody tr').forEach(tr => {
            const cells = Array.from(tr.querySelectorAll('th, td'));
            // Skip action column (last th with no text / contains button)
            const data = cells.slice(0, -1).map(c => c.textContent.trim());
            if (data.some(v => v !== '')) rows.push(data);
        });
        return rows;
    }

    document.getElementById('exportDivExcel').addEventListener('click', function (e) {
        e.preventDefault();
        const table = getActiveTable();
        if (!table) return;
        const ws = XLSX.utils.aoa_to_sheet(tableToArray(table));
        const wb = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(wb, ws, activeTabLabel());
        XLSX.writeFile(wb, `dashboard-${activeTabLabel().replace(/\s+/g,'-')}-${new Date().toISOString().slice(0,10)}.xlsx`);
    });

    document.getElementById('exportDivPdf').addEventListener('click', function (e) {
        e.preventDefault();
        const table = getActiveTable();
        if (!table) return;
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF({ orientation: 'landscape' });
        const rows = tableToArray(table);
        const head = [rows[0]];
        const body = rows.slice(1);
        const label = activeTabLabel();
        doc.setFontSize(13);
        doc.text(`MGVCL — ${label}`, 14, 15);
        doc.setFontSize(9);
        doc.text(`Exported: ${new Date().toLocaleString('en-IN')}`, 14, 22);
        doc.autoTable({ head, body, startY: 27, styles: { fontSize: 8 }, headStyles: { fillColor: [26, 58, 92] } });
        doc.save(`dashboard-${label.replace(/\s+/g,'-')}-${new Date().toISOString().slice(0,10)}.pdf`);
    });

    document.getElementById('exportDivWhatsapp').addEventListener('click', function (e) {
        e.preventDefault();
        const table = getActiveTable();
        if (!table) return;

        const label  = activeTabLabel();
        const isSubDiv = label.toLowerCase().includes('sub');
        const now    = new Date();
        const dateStr = now.toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' });
        const timeStr = now.toLocaleTimeString('en-IN', { hour: '2-digit', minute: '2-digit' });

        let msg = `📊 *MGVCL ${label} Status Report*\n`;
        msg += `📅 Date: ${dateStr} ${timeStr}\n\n`;

        table.querySelectorAll('tbody tr').forEach(tr => {
            const cells = tr.querySelectorAll('td');
            if (cells.length < 5) return;
            const name    = isSubDiv ? `${cells[0].textContent.trim()} (${cells[1].textContent.trim()})` : cells[0].textContent.trim();
            const on      = isSubDiv ? cells[2].textContent.trim() : cells[1].textContent.trim();
            const partial = isSubDiv ? cells[3].textContent.trim() : cells[2].textContent.trim();
            const off     = isSubDiv ? cells[4].textContent.trim() : cells[3].textContent.trim();
            const total   = isSubDiv ? cells[5].textContent.trim() : cells[4].textContent.trim();
            msg += `*${name}*\n`;
            msg += `  ✅ ON: ${on}  ⚠️ Partial: ${partial}  ❌ OFF: ${off}  📌 Total: ${total}\n`;
        });

        msg += `\n_Exported from MGVCL Portal_`;

        document.getElementById('waText').value = msg;
        new bootstrap.Modal(document.getElementById('waModal')).show();
    });

    document.getElementById('copyWa').addEventListener('click', function () {
        navigator.clipboard.writeText(document.getElementById('waText').value).then(function () {
            new bootstrap.Toast(document.getElementById('waCopyToast')).show();
        });
    });
</script>
<script src="https://cdn.sheetjs.com/xlsx-0.20.3/package/dist/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>
@endpush
