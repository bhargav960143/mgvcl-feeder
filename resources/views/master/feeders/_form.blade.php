<div class="row g-3">
    <div class="col-12">
        <label class="form-label fw-semibold">Substation <span class="text-danger">*</span></label>
        <select name="substation_id" class="form-select" required>
            <option value="">Select substation...</option>
            @foreach($substations as $ss)
            <option value="{{ $ss->id }}" {{ old('substation_id', $feeder?->substation_id) == $ss->id ? 'selected' : '' }}>
                {{ $ss->name }} — {{ $ss->subDivision->name }}
            </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-8">
        <label class="form-label fw-semibold">Feeder Name <span class="text-danger">*</span></label>
        <input type="text" name="name" class="form-control" value="{{ old('name', $feeder?->name) }}" required>
    </div>
    <div class="col-md-4">
        <label class="form-label fw-semibold">TND Code <span class="text-danger">*</span></label>
        <input type="text" name="tnd_code" class="form-control" value="{{ old('tnd_code', $feeder?->tnd_code) }}" required>
    </div>
    <div class="col-md-4">
        <label class="form-label fw-semibold">Category <span class="text-danger">*</span></label>
        <select name="category" class="form-select" required>
            @foreach($categories as $cat)
            <option value="{{ $cat }}" {{ old('category', $feeder?->category) == $cat ? 'selected' : '' }}>{{ $cat }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4">
        <label class="form-label fw-semibold">Total Consumers <span class="text-danger">*</span></label>
        <input type="number" name="total_consumer" class="form-control" min="0" value="{{ old('total_consumer', $feeder?->total_consumer ?? 0) }}" required>
    </div>
    <div class="col-md-4">
        <label class="form-label fw-semibold">Total TC <span class="text-danger">*</span></label>
        <input type="number" name="total_tc" class="form-control" min="0" value="{{ old('total_tc', $feeder?->total_tc ?? 0) }}" required>
    </div>
</div>
