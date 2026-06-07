@extends('layouts.app')
@section('title', 'Add Category — Master')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0 fw-bold"><i class="bi bi-tags me-2"></i>Add Feeder Category</h4>
    <a href="{{ route('master.feeder-categories.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Back
    </a>
</div>
<div class="card border-0 shadow-sm" style="max-width:480px;">
    <div class="card-body">
        <form method="POST" action="{{ route('master.feeder-categories.store') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label fw-semibold">Category Name <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control text-uppercase @error('name') is-invalid @enderror"
                       value="{{ old('name') }}" placeholder="e.g. URBAN" required maxlength="50"
                       oninput="this.value=this.value.toUpperCase()">
                <div class="form-text">Uppercase letters, digits, hyphens, underscores only.</div>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <button type="submit" class="btn btn-primary">Save Category</button>
        </form>
    </div>
</div>
@endsection
