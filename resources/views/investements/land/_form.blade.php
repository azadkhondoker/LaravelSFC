<div class="row">
    <div class="col-md-6 mb-3">
        <label for="name" class="form-label">Land title/name <span class="text-danger">*</span></label>
        <input id="name" name="name" value="{{ old('name', $land->name ?? '') }}" class="form-control @error('name') is-invalid @enderror" maxlength="150" required>
        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6 mb-3">
        <label for="location" class="form-label">Location</label>
        <input id="location" name="location" value="{{ old('location', $land->location ?? '') }}" class="form-control @error('location') is-invalid @enderror">
        @error('location')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6 mb-3">
        <label for="purchase_price" class="form-label">Purchase price <span class="text-danger">*</span></label>
        <input id="purchase_price" type="number" name="purchase_price" value="{{ old('purchase_price', $land->purchase_price ?? '') }}" min="0.01" step="0.01" class="form-control @error('purchase_price') is-invalid @enderror" required>
        @error('purchase_price')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-3 mb-3">
        <label for="area" class="form-label">Land area/quantity <span class="text-danger">*</span></label>
        <input id="area" type="number" name="area" value="{{ old('area', $land->area ?? '') }}" min="0.001" step="0.001" class="form-control @error('area') is-invalid @enderror" required>
        @error('area')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-3 mb-3">
        <label for="area_unit" class="form-label">Area unit <span class="text-danger">*</span></label>
        <input id="area_unit" name="area_unit" value="{{ old('area_unit', $land->area_unit ?? 'decimal') }}" maxlength="30" placeholder="decimal, acre, katha..." class="form-control @error('area_unit') is-invalid @enderror" required>
        @error('area_unit')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6 mb-3">
        <label for="purchased_at" class="form-label">Purchase date <span class="text-danger">*</span></label>
        <input id="purchased_at" type="date" name="purchased_at" value="{{ old('purchased_at', isset($land) ? $land->purchased_at->format('Y-m-d') : now()->format('Y-m-d')) }}" class="form-control @error('purchased_at') is-invalid @enderror" required>
        @error('purchased_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6 mb-3">
        <label for="deed" class="form-label">Deed document (PDF, max 10 MB)</label>
        <input id="deed" type="file" name="deed" accept="application/pdf,.pdf" class="form-control @error('deed') is-invalid @enderror">
        @error('deed')<div class="invalid-feedback">{{ $message }}</div>@enderror
        @isset($land)
            @if ($land->deed_path)
                <a class="d-inline-block mt-2" href="{{ route('investments.land.deed', $land) }}">View current deed</a>
            @endif
        @endisset
    </div>
</div>
