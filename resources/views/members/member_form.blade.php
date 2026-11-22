@csrf

<div class="member-form-grid">
    <div class="member-field">
        <label for="name">Full name <span class="text-danger">*</span></label>
        <input
            id="name"
            type="text"
            name="name"
            value="{{ old('name', $member->name ?? '') }}"
            class="form-control @error('name') is-invalid @enderror"
            maxlength="100"
            autocomplete="name"
            required
        >
        @error('name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="member-field">
        <label for="email">Email address <span class="text-danger">*</span></label>
        <input
            id="email"
            type="email"
            name="email"
            value="{{ old('email', $member->email ?? '') }}"
            class="form-control @error('email') is-invalid @enderror"
            maxlength="100"
            autocomplete="email"
            required
        >
        @error('email')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="member-field">
        <label for="phone">Phone number</label>
        <input
            id="phone"
            type="tel"
            name="phone"
            value="{{ old('phone', $member->phone ?? '') }}"
            class="form-control @error('phone') is-invalid @enderror"
            maxlength="20"
            inputmode="tel"
            autocomplete="tel"
        >
        @error('phone')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="member-field">
        <label for="nid">National ID</label>
        <input
            id="nid"
            type="number"
            name="nid"
            value="{{ old('nid', $member->nid ?? '') }}"
            class="form-control @error('nid') is-invalid @enderror"
            inputmode="numeric"
        >
        @error('nid')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="member-field">
        <label id="gender-label">Gender <span class="text-danger">*</span></label>
        @php($selectedGender = (string) old('gender', isset($member) ? $member->gender : ''))
        <div class="member-gender-options" role="radiogroup" aria-labelledby="gender-label">
            <label class="member-gender-option">
                <input type="radio" name="gender" value="0" @checked($selectedGender === '0') required>
                <span>Male</span>
            </label>
            <label class="member-gender-option">
                <input type="radio" name="gender" value="1" @checked($selectedGender === '1') required>
                <span>Female</span>
            </label>
        </div>
        @error('gender')
            <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
    </div>

    <div class="member-field">
        <label for="joining_date">Joining date <span class="text-danger">*</span></label>
        <input
            id="joining_date"
            type="date"
            name="joining_date"
            value="{{ old('joining_date', isset($member) && $member->joining_date ? $member->joining_date->format('Y-m-d') : now()->format('Y-m-d')) }}"
            class="form-control @error('joining_date') is-invalid @enderror"
            required
        >
        @error('joining_date')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="member-field">
        <label for="installment_amount">Fixed installment amount <span class="text-danger">*</span></label>
        <input
            id="installment_amount"
            type="number"
            name="installment_amount"
            value="{{ old('installment_amount', $member->installment_amount ?? '') }}"
            class="form-control @error('installment_amount') is-invalid @enderror"
            min="0.01"
            step="0.01"
            inputmode="decimal"
            required
        >
        <small class="form-text">This is the only amount accepted for this member's installment deposits.</small>
        @error('installment_amount')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="member-field">
        <label for="status">Membership status <span class="text-danger">*</span></label>
        <select
            id="status"
            name="status"
            class="form-select @error('status') is-invalid @enderror"
            required
        >
            @foreach (['active' => 'Active', 'inactive' => 'Inactive'] as $value => $label)
                <option value="{{ $value }}" @selected(old('status', $member->status ?? 'active') === $value)>
                    {{ $label }}
                </option>
            @endforeach
        </select>
        @error('status')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="member-field">
        <label for="address">Address</label>
        <textarea
            id="address"
            name="address"
            rows="3"
            class="form-control @error('address') is-invalid @enderror"
            autocomplete="street-address"
        >{{ old('address', $member->address ?? '') }}</textarea>
        @error('address')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>
