@extends('layouts.app')
@section('title', 'Edit Division')
@section('content')
<div class="d-flex align-items-center mb-3">
    <a href="{{ route('master.divisions.index') }}" class="btn btn-sm btn-outline-secondary me-3"><i class="bi bi-arrow-left"></i></a>
    <h4 class="mb-0 fw-bold">Edit Division</h4>
</div>
<div class="card border-0 shadow-sm" style="max-width:450px;">
    <div class="card-body p-4">
        @if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif
        <form method="POST" action="{{ route('master.divisions.update', $division) }}">
            @csrf @method('PUT')
            <div class="mb-3">
                <label class="form-label fw-semibold">Circle <span class="text-danger">*</span></label>
                <select name="circle_id" class="form-select" required>
                    @foreach($circles as $c)
                    <option value="{{ $c->id }}" {{ old('circle_id', $division->circle_id) == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Division Name <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $division->name) }}" required>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle me-1"></i> Update</button>
                <a href="{{ route('master.divisions.index') }}" class="btn btn-light">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
