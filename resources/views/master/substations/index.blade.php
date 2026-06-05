@extends('layouts.app')
@section('title', 'Substations')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0 fw-bold"><i class="bi bi-grid me-2"></i>Substations</h4>
    <a href="{{ route('master.substations.create') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-circle me-1"></i> Add Substation
    </a>
</div>
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-4">
                <select name="sub_division_id" class="form-select form-select-sm">
                    <option value="">All Sub Divisions</option>
                    @foreach($subDivisions as $sd)
                    <option value="{{ $sd->id }}" {{ request('sub_division_id') == $sd->id ? 'selected' : '' }}>{{ $sd->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <button class="btn btn-sm btn-primary">Filter</button>
                <a href="{{ route('master.substations.index') }}" class="btn btn-sm btn-outline-secondary">Clear</a>
            </div>
        </form>
    </div>
</div>
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <table class="table table-hover mb-0" data-dt>
            <thead class="table-light">
                <tr>
                    <th class="ps-3">#</th><th>Substation Name</th><th>Sub Division</th><th>Division</th>
                    <th class="text-center">Feeders</th><th class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($substations as $ss)
                <tr>
                    <td class="ps-3 text-muted small">{{ $loop->iteration }}</td>
                    <td class="fw-semibold">{{ $ss->name }}</td>
                    <td class="small">{{ $ss->subDivision->name }}</td>
                    <td class="small text-muted">{{ $ss->subDivision->division->name }}</td>
                    <td class="text-center">{{ $ss->feeders_count }}</td>
                    <td class="text-center">
                        <a href="{{ route('master.substations.edit', $ss) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                        <form method="POST" action="{{ route('master.substations.destroy', $ss) }}" class="d-inline"
                              onsubmit="return confirm('Delete {{ $ss->name }}?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
