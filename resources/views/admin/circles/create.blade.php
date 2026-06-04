@extends('layouts.app')
@section('title', 'Add Circle')
@section('content')
<div class="d-flex align-items-center mb-3">
    <a href="{{ route('admin.circles.index') }}" class="btn btn-sm btn-outline-secondary me-3"><i class="bi bi-arrow-left"></i></a>
    <h4 class="mb-0 fw-bold">Add Circle</h4>
</div>
<div class="card border-0 shadow-sm" style="max-width:400px;">
    <div class="card-body p-4">
        @if($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
        @endif
        <form method="POST" action="{{ route('admin.circles.store') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label fw-semibold">Circle Name <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control" value="{{ old('name') }}" required autofocus>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle me-1"></i> Create</button>
                <a href="{{ route('admin.circles.index') }}" class="btn btn-light">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
