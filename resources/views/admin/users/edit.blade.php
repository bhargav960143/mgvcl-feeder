@extends('layouts.app')
@section('title', 'Edit User')
@section('content')
<div class="d-flex align-items-center mb-3">
    <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-outline-secondary me-3">
        <i class="bi bi-arrow-left"></i>
    </a>
    <h4 class="mb-0 fw-bold">Edit User — {{ $user->name }}</h4>
</div>

<div class="card border-0 shadow-sm" style="max-width:700px;">
    <div class="card-body p-4">
        <form method="POST" action="{{ route('admin.users.update', $user) }}">
            @csrf @method('PUT')
            @include('admin.users._form', compact('user'))
            <div class="d-flex gap-2 mt-3">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle me-1"></i> Update User
                </button>
                <a href="{{ route('admin.users.index') }}" class="btn btn-light">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
