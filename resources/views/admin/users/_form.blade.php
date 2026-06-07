@if($errors->any())
<div class="alert alert-danger mb-3">
    <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label fw-semibold">Name <span class="text-danger">*</span></label>
        <input type="text" name="name" class="form-control" value="{{ old('name', $user?->name) }}" required>
    </div>
    <div class="col-md-6">
        <label class="form-label fw-semibold">Employee ID</label>
        <input type="text" name="employee_id" class="form-control" value="{{ old('employee_id', $user?->employee_id) }}">
    </div>
    <div class="col-md-6">
        <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
        <input type="email" name="email" class="form-control" value="{{ old('email', $user?->email) }}" required>
    </div>
    <div class="col-md-6">
        <label class="form-label fw-semibold">Phone</label>
        <input type="text" name="phone" class="form-control" value="{{ old('phone', $user?->phone) }}">
    </div>
    <div class="col-md-6">
        <label class="form-label fw-semibold">Password {{ $user ? '(leave blank to keep)' : '' }} <span class="text-danger">{{ $user ? '' : '*' }}</span></label>
        <input type="password" name="password" class="form-control" {{ $user ? '' : 'required' }} autocomplete="new-password">
    </div>
    <div class="col-md-6">
        <label class="form-label fw-semibold">Confirm Password</label>
        <input type="password" name="password_confirmation" class="form-control" autocomplete="new-password">
    </div>

    <div class="col-md-6">
        <label class="form-label fw-semibold">Role <span class="text-danger">*</span></label>
        <select name="role" id="roleSelect" class="form-select" required>
            <option value="">Select role...</option>
            @foreach($roles as $role)
            <option value="{{ $role }}"
                {{ old('role', $user?->getRoleNames()->first()) == $role ? 'selected' : '' }}>
                {{ ucwords(str_replace('_', ' ', $role)) }}
            </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-6" id="jurisdictionWrapper">
        <label class="form-label fw-semibold">Jurisdiction</label>

        {{-- Single hidden field — JS keeps this in sync with the active section --}}
        <input type="hidden" name="jurisdiction_type" id="jurisdictionType"
               value="{{ old('jurisdiction_type', $user?->jurisdiction_type ?? 'global') }}">

        <div id="circleJurisdiction" class="d-none">
            <select name="jurisdiction_id" id="selectCircle" class="form-select" disabled>
                <option value="">Select circle...</option>
                @foreach($circles as $c)
                <option value="{{ $c->id }}" {{ old('jurisdiction_id', $user?->jurisdiction_id) == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                @endforeach
            </select>
        </div>
        <div id="divisionJurisdiction" class="d-none">
            <select name="jurisdiction_id" id="selectDivision" class="form-select" disabled>
                <option value="">Select division...</option>
                @foreach($divisions as $d)
                <option value="{{ $d->id }}" {{ old('jurisdiction_id', $user?->jurisdiction_id) == $d->id ? 'selected' : '' }}>{{ $d->name }} ({{ $d->circle->name }})</option>
                @endforeach
            </select>
        </div>
        <div id="subDivisionJurisdiction" class="d-none">
            <select name="jurisdiction_id" id="selectSubDivision" class="form-select" disabled>
                <option value="">Select sub division...</option>
                @foreach($subDivisions as $sd)
                <option value="{{ $sd->id }}" {{ old('jurisdiction_id', $user?->jurisdiction_id) == $sd->id ? 'selected' : '' }}>{{ $sd->name }} ({{ $sd->division->name }})</option>
                @endforeach
            </select>
        </div>
        <div id="globalJurisdiction" class="d-none">
            <p class="text-muted small mt-1 mb-0">Admin has global access — no jurisdiction needed.</p>
        </div>
    </div>
</div>

@push('scripts')
<script>
const roleSelect      = document.getElementById('roleSelect');
const jtInput         = document.getElementById('jurisdictionType');

const sectionMap = {
    admin:                { divId: 'globalJurisdiction',      selectId: null,                type: 'global'      },
    circle:               { divId: 'circleJurisdiction',      selectId: 'selectCircle',      type: 'circle'      },
    division_manager:     { divId: 'divisionJurisdiction',    selectId: 'selectDivision',    type: 'division'    },
    sub_division_manager: { divId: 'subDivisionJurisdiction', selectId: 'selectSubDivision', type: 'sub_division' },
};

function updateJurisdiction() {
    // Hide all sections and disable all selects
    Object.values(sectionMap).forEach(({ divId, selectId }) => {
        document.getElementById(divId)?.classList.add('d-none');
        if (selectId) {
            const sel = document.getElementById(selectId);
            if (sel) sel.disabled = true;
        }
    });

    const cfg = sectionMap[roleSelect.value];
    if (!cfg) return;

    // Show active section
    document.getElementById(cfg.divId)?.classList.remove('d-none');

    // Enable active select and sync jurisdiction_type
    if (cfg.selectId) {
        const sel = document.getElementById(cfg.selectId);
        if (sel) sel.disabled = false;
    }
    jtInput.value = cfg.type;
}

roleSelect.addEventListener('change', updateJurisdiction);
updateJurisdiction();
</script>
@endpush
