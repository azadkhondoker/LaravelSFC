@csrf
<div class="row">
    <div class="col-md-6 mb-3">
        <label for="name" class="form-label">Full name <span class="text-danger">*</span></label>
        <input id="name" name="name" value="{{ old('name', $client->name ?? '') }}" class="form-control @error('name') is-invalid @enderror" maxlength="100" required>
        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6 mb-3">
        <label for="email" class="form-label">Email</label>
        <input id="email" type="email" name="email" value="{{ old('email', $client->email ?? '') }}" class="form-control @error('email') is-invalid @enderror" maxlength="100">
        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6 mb-3">
        <label for="phone" class="form-label">Phone</label>
        <input id="phone" type="tel" name="phone" value="{{ old('phone', $client->phone ?? '') }}" class="form-control @error('phone') is-invalid @enderror" maxlength="20">
        @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6 mb-3">
        <label for="nid" class="form-label">National ID</label>
        <input id="nid" name="nid" value="{{ old('nid', $client->nid ?? '') }}" class="form-control @error('nid') is-invalid @enderror" maxlength="30">
        @error('nid')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-12 mb-3">
        <label for="address" class="form-label">Address</label>
        <textarea id="address" name="address" rows="3" class="form-control @error('address') is-invalid @enderror">{{ old('address', $client->address ?? '') }}</textarea>
        @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>
