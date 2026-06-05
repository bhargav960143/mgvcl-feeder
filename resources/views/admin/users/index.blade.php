@extends('layouts.app')
@section('title', 'Users — Admin')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0 fw-bold"><i class="bi bi-people me-2"></i>Users</h4>
    <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-circle me-1"></i> Add User
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0" data-dt>
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">#</th>
                        <th>Name</th>
                        <th>Employee ID</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Jurisdiction</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                    <tr>
                        <td class="ps-3 text-muted small">{{ $loop->iteration }}</td>
                        <td class="fw-semibold">{{ $user->name }}</td>
                        <td><code>{{ $user->employee_id ?? '—' }}</code></td>
                        <td class="small">{{ $user->email }}</td>
                        <td>
                            <span class="badge bg-primary">{{ $user->getRoleNames()->first() ?? '—' }}</span>
                        </td>
                        <td class="small text-muted">
                            {{ ucfirst(str_replace('_', ' ', $user->jurisdiction_type)) }}
                            @if($user->jurisdiction_id)
                                — {{ $user->jurisdictionLabel() }}
                            @endif
                        </td>
                        <td class="text-center">
                            <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-pencil"></i>
                            </a>
                            @if($user->id !== auth()->id())
                            <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="d-inline"
                                  onsubmit="return confirm('Delete user {{ $user->name }}?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
