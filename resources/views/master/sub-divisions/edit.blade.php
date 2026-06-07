@extends('layouts.app')
@section('title', 'Edit Sub Division')
@section('content')
<div class="d-flex align-items-center mb-3">
    <a href="{{ route('master.sub-divisions.index') }}" class="btn btn-sm btn-outline-secondary me-3"><i class="bi bi-arrow-left"></i></a>
    <h4 class="mb-0 fw-bold">Edit Sub Division</h4>
</div>
<div class="card border-0 shadow-sm" style="max-width:450px;">
    <div class="card-body p-4">
        @if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif
        <form method="POST" action="{{ route('master.sub-divisions.update', $subDivision) }}">
            @csrf @method('PUT')
            <div class="mb-3">
                <label class="form-label fw-semibold">Division <span class="text-danger">*</span></label>
                <select name="division_id" class="form-select" required>
                    <option value="">Select division...</option>
                    @foreach($divisions as $d)
                    <option value="{{ $d->id }}" {{ old('division_id', $subDivision->division_id) == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Sub Division Name <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $subDivision->name) }}" required>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle me-1"></i> Update</button>
                <a href="{{ route('master.sub-divisions.index') }}" class="btn btn-light">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
