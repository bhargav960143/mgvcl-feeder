@extends('layouts.app')
@section('title', 'Feeder Master')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0 fw-bold"><i class="bi bi-reception-4 me-2"></i>Feeder Master</h4>
    <a href="{{ route('master.feeders.create') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-circle me-1"></i> Add Feeder
    </a>
</div>
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-4">
                <select name="substation_id" class="form-select form-select-sm">
                    <option value="">All Substations</option>
                    @foreach($substations as $ss)
                    <option value="{{ $ss->id }}" {{ request('substation_id') == $ss->id ? 'selected' : '' }}>{{ $ss->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <button class="btn btn-sm btn-primary">Filter</button>
                <a href="{{ route('master.feeders.index') }}" class="btn btn-sm btn-outline-secondary">Clear</a>
            </div>
        </form>
    </div>
</div>
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0" data-dt>
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">#</th><th>Feeder Name</th><th>TND Code</th>
                        <th>Substation</th><th>Category</th>
                        <th class="text-center">Consumers</th><th class="text-center">TC</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($feeders as $feeder)
                    <tr>
                        <td class="ps-3 text-muted small">{{ $loop->iteration }}</td>
                        <td class="fw-semibold">{{ $feeder->name }}</td>
                        <td><code>{{ $feeder->tnd_code }}</code></td>
                        <td class="small">{{ $feeder->substation->name }}</td>
                        <td><span class="badge bg-light text-dark border">{{ $feeder->category }}</span></td>
                        <td class="text-center small">{{ number_format($feeder->total_consumer) }}</td>
                        <td class="text-center small">{{ $feeder->total_tc }}</td>
                        <td class="text-center">
                            <a href="{{ route('master.feeders.edit', $feeder) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                            <form method="POST" action="{{ route('master.feeders.destroy', $feeder) }}" class="d-inline"
                                  onsubmit="return confirm('Delete feeder {{ $feeder->name }}?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center py-4 text-muted">No feeders found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
