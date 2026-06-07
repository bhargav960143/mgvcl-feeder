@extends('layouts.app')
@section('title', 'Feeder Categories — Master')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0 fw-bold"><i class="bi bi-tags me-2"></i>Feeder Categories</h4>
    <a href="{{ route('master.feeder-categories.create') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-circle me-1"></i> Add Category
    </a>
</div>
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <table class="table table-hover mb-0" data-dt>
            <thead class="table-light">
                <tr>
                    <th class="ps-3">#</th>
                    <th>Name</th>
                    <th class="text-center">Feeders</th>
                    <th class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($categories as $category)
                <tr>
                    <td class="ps-3 text-muted small">{{ $loop->iteration }}</td>
                    <td><span class="badge bg-light text-dark border fw-semibold">{{ $category->name }}</span></td>
                    <td class="text-center">{{ $category->feeders_count }}</td>
                    <td class="text-center">
                        <a href="{{ route('master.feeder-categories.edit', $category) }}" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <form method="POST" action="{{ route('master.feeder-categories.destroy', $category) }}" class="d-inline"
                              onsubmit="return confirm('Delete category {{ $category->name }}?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger" {{ $category->feeders_count > 0 ? 'disabled title=Category in use' : '' }}>
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
