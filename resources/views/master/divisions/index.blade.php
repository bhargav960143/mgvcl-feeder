@extends('layouts.app')
@section('title', 'Divisions')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0 fw-bold"><i class="bi bi-building me-2"></i>Divisions</h4>
    <a href="{{ route('master.divisions.create') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-circle me-1"></i> Add Division
    </a>
</div>
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-3">#</th><th>Division Name</th><th>Circle</th>
                    <th class="text-center">Sub Divisions</th><th class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($divisions as $div)
                <tr>
                    <td class="ps-3 text-muted small">{{ $divisions->firstItem() + $loop->index }}</td>
                    <td class="fw-semibold">{{ $div->name }}</td>
                    <td class="small text-muted">{{ $div->circle->name }}</td>
                    <td class="text-center">{{ $div->sub_divisions_count }}</td>
                    <td class="text-center">
                        <a href="{{ route('master.divisions.edit', $div) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                        <form method="POST" action="{{ route('master.divisions.destroy', $div) }}" class="d-inline"
                              onsubmit="return confirm('Delete {{ $div->name }}?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center py-4 text-muted">No divisions found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($divisions->hasPages())<div class="card-footer bg-white">{{ $divisions->links() }}</div>@endif
</div>
@endsection
