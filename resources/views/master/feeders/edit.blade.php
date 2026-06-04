@extends('layouts.app')
@section('title', 'Edit Feeder')
@section('content')
<div class="d-flex align-items-center mb-3">
    <a href="{{ route('master.feeders.index') }}" class="btn btn-sm btn-outline-secondary me-3"><i class="bi bi-arrow-left"></i></a>
    <h4 class="mb-0 fw-bold">Edit Feeder — {{ $feeder->name }}</h4>
</div>
<div class="card border-0 shadow-sm" style="max-width:600px;">
    <div class="card-body p-4">
        @if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif
        <form method="POST" action="{{ route('master.feeders.update', $feeder) }}">
            @csrf @method('PUT')
            @include('master.feeders._form', compact('feeder'))
            <div class="d-flex gap-2 mt-3">
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle me-1"></i> Update Feeder</button>
                <a href="{{ route('master.feeders.index') }}" class="btn btn-light">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
