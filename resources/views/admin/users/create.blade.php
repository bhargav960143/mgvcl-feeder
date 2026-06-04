@extends('layouts.app')
@section('title', 'Add User')
@section('content')
<div class="d-flex align-items-center mb-3">
    <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-outline-secondary me-3">
        <i class="bi bi-arrow-left"></i>
    </a>
    <h4 class="mb-0 fw-bold">Add User</h4>
</div>

<div class="card border-0 shadow-sm" style="max-width:700px;">
    <div class="card-body p-4">
        <form method="POST" action="{{ route('admin.users.store') }}">
            @csrf
            @include('admin.users._form', ['user' => null])
            <div class="d-flex gap-2 mt-3">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle me-1"></i> Create User
                </button>
                <a href="{{ route('admin.users.index') }}" class="btn btn-light">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
