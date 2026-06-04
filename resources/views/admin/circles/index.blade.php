@extends('layouts.app')
@section('title', 'Circles — Admin')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0 fw-bold"><i class="bi bi-diagram-3 me-2"></i>Circles</h4>
    <a href="{{ route('admin.circles.create') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-circle me-1"></i> Add Circle
    </a>
</div>
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-3">#</th>
                    <th>Name</th>
                    <th class="text-center">Divisions</th>
                    <th class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($circles as $circle)
                <tr>
                    <td class="ps-3 text-muted small">{{ $circles->firstItem() + $loop->index }}</td>
                    <td class="fw-semibold">{{ $circle->name }}</td>
                    <td class="text-center">{{ $circle->divisions_count }}</td>
                    <td class="text-center">
                        <a href="{{ route('admin.circles.edit', $circle) }}" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <form method="POST" action="{{ route('admin.circles.destroy', $circle) }}" class="d-inline"
                              onsubmit="return confirm('Delete circle {{ $circle->name }}? This will delete all divisions under it.')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="4" class="text-center py-4 text-muted">No circles found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($circles->hasPages())
    <div class="card-footer bg-white">{{ $circles->links() }}</div>
    @endif
</div>
@endsection
