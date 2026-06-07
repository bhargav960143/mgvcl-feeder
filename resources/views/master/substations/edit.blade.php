@extends('layouts.app')
@section('title', 'Edit Substation')
@section('content')
<div class="d-flex align-items-center mb-3">
    <a href="{{ route('master.substations.index') }}" class="btn btn-sm btn-outline-secondary me-3"><i class="bi bi-arrow-left"></i></a>
    <h4 class="mb-0 fw-bold">Edit Substation</h4>
</div>
<div class="card border-0 shadow-sm" style="max-width:450px;">
    <div class="card-body p-4">
        @if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif
        <form method="POST" action="{{ route('master.substations.update', $substation) }}">
            @csrf @method('PUT')
            <div class="mb-3">
                <label class="form-label fw-semibold">Sub Division <span class="text-danger">*</span></label>
                <select name="sub_division_id" class="form-select" required>
                    <option value="">Select sub division...</option>
                    @foreach($subDivisions as $sd)
                    <option value="{{ $sd->id }}" {{ old('sub_division_id', $substation->sub_division_id) == $sd->id ? 'selected' : '' }}>
                        {{ $sd->name }} — {{ $sd->division->name }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Substation Name <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $substation->name) }}" required>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle me-1"></i> Update</button>
                <a href="{{ route('master.substations.index') }}" class="btn btn-light">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
