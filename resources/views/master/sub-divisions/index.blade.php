@extends('layouts.app')
@section('title', 'Sub Divisions')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0 fw-bold"><i class="bi bi-diagram-2 me-2"></i>Sub Divisions</h4>
    <a href="{{ route('master.sub-divisions.create') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-circle me-1"></i> Add Sub Division
    </a>
</div>
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <table class="table table-hover mb-0" data-dt>
            <thead class="table-light">
                <tr>
                    <th class="ps-3">#</th><th>Sub Division</th><th>Division</th><th>Circle</th>
                    <th class="text-center">Substations</th><th class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($subDivisions as $sd)
                <tr>
                    <td class="ps-3 text-muted small">{{ $loop->iteration }}</td>
                    <td class="fw-semibold">{{ $sd->name }}</td>
                    <td class="small">{{ $sd->division->name }}</td>
                    <td class="small text-muted">{{ $sd->division->circle->name }}</td>
                    <td class="text-center">{{ $sd->substations_count }}</td>
                    <td class="text-center">
                        <a href="{{ route('master.sub-divisions.edit', $sd) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                        <form method="POST" action="{{ route('master.sub-divisions.destroy', $sd) }}" class="d-inline"
                              onsubmit="return confirm('Delete {{ $sd->name }}?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted py-4">No sub-divisions found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
