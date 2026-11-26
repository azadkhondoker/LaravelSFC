@php
    $borrowerType = old('borrower_type', isset($loan) && $loan->loan_client_id ? 'client' : 'member');
    $selectedMember = (string) old('member_id', $loan->member_id ?? '');
    $selectedClient = (string) old('loan_client_id', $loan->loan_client_id ?? '');
    $savedGuarantors = isset($loan) ? $loan->guarantors->keyBy('position') : collect();
    $collateralRows = old('collaterals');
    if ($collateralRows === null) {
        $collateralRows = isset($loan)
            ? $loan->collaterals->map(fn ($item) => [
                'id' => $item->id,
                'asset_type' => $item->asset_type,
                'description' => $item->description,
                'estimated_value' => $item->estimated_value,
            ])->all()
            : [];
    }
    if (count($collateralRows) === 0) $collateralRows[] = [];
@endphp

<div class="mb-3">
    <label for="borrower_type" class="form-label">Borrower type <span class="text-danger">*</span></label>
    <select id="borrower_type" name="borrower_type" class="form-select @error('borrower_type') is-invalid @enderror" required>
        <option value="member" @selected($borrowerType === 'member')>Existing member</option>
        <option value="client" @selected($borrowerType === 'client')>Non-member client</option>
    </select>
    @error('borrower_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div id="member-borrower" class="mb-3">
    <label for="member_id" class="form-label">Select member <span class="text-danger">*</span></label>
    <select id="member_id" name="member_id" class="form-select @error('member_id') is-invalid @enderror">
        <option value="">Choose a member</option>
        @foreach ($members as $member)
            <option value="{{ $member->id }}"
                data-name="{{ $member->name }}"
                data-code="{{ $member->member_code }}"
                data-email="{{ $member->email }}"
                data-phone="{{ $member->phone }}"
                data-nid="{{ $member->nid }}"
                data-address="{{ $member->address }}"
                @selected($selectedMember === (string) $member->id)>
                {{ $member->member_code }} — {{ $member->name }}
            </option>
        @endforeach
    </select>
    @error('member_id')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
</div>

<div id="client-borrower" class="mb-3">
    <div class="d-flex justify-content-between align-items-center">
        <label for="loan_client_id" class="form-label">Select registered client <span class="text-danger">*</span></label>
        <a href="{{ route('investments.clients.create') }}" target="_blank" rel="noopener">Register a client</a>
    </div>
    <select id="loan_client_id" name="loan_client_id" class="form-select @error('loan_client_id') is-invalid @enderror">
        <option value="">Choose a client</option>
        @foreach ($clients as $client)
            <option value="{{ $client->id }}"
                data-name="{{ $client->name }}"
                data-email="{{ $client->email }}"
                data-phone="{{ $client->phone }}"
                data-nid="{{ $client->nid }}"
                data-address="{{ $client->address }}"
                @selected($selectedClient === (string) $client->id)>
                {{ $client->name }}{{ $client->phone ? ' — ' . $client->phone : '' }}
            </option>
        @endforeach
    </select>
    @error('loan_client_id')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
</div>

<div id="borrower-details" class="alert alert-light border d-none" aria-live="polite">
    <strong id="borrower-name"></strong>
    <div><span id="borrower-code"></span></div>
    <div><span id="borrower-email"></span></div>
    <div><span id="borrower-phone"></span></div>
    <div><span id="borrower-nid"></span></div>
    <div><span id="borrower-address"></span></div>
</div>

<h3 class="h5 mt-4">Guarantors <span class="text-danger">*</span></h3>
<p class="text-muted">Provide details for exactly two guarantors. Both must confirm their liability if the borrower fails to repay.</p>
@for ($index = 0; $index < 2; $index++)
    @php $guarantor = $savedGuarantors->get($index + 1); @endphp
    <div class="card mb-3">
        <div class="card-header">Guarantor {{ $index + 1 }}</div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label" for="guarantor-{{ $index }}-name">Full name <span class="text-danger">*</span></label>
                    <input id="guarantor-{{ $index }}-name" name="guarantors[{{ $index }}][name]" value="{{ old("guarantors.$index.name", $guarantor?->name ?? '') }}" class="form-control @error("guarantors.$index.name") is-invalid @enderror" maxlength="100" required>
                    @error("guarantors.$index.name")<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label" for="guarantor-{{ $index }}-phone">Phone <span class="text-danger">*</span></label>
                    <input id="guarantor-{{ $index }}-phone" name="guarantors[{{ $index }}][phone]" value="{{ old("guarantors.$index.phone", $guarantor?->phone ?? '') }}" class="form-control @error("guarantors.$index.phone") is-invalid @enderror" maxlength="20" required>
                    @error("guarantors.$index.phone")<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label" for="guarantor-{{ $index }}-nid">NID <span class="text-danger">*</span></label>
                    <input id="guarantor-{{ $index }}-nid" name="guarantors[{{ $index }}][nid]" value="{{ old("guarantors.$index.nid", $guarantor?->nid ?? '') }}" class="form-control @error("guarantors.$index.nid") is-invalid @enderror" maxlength="30" required>
                    @error("guarantors.$index.nid")<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12 mb-3">
                    <label class="form-label" for="guarantor-{{ $index }}-address">Address <span class="text-danger">*</span></label>
                    <textarea id="guarantor-{{ $index }}-address" name="guarantors[{{ $index }}][address]" rows="2" class="form-control @error("guarantors.$index.address") is-invalid @enderror" required>{{ old("guarantors.$index.address", $guarantor?->address ?? '') }}</textarea>
                    @error("guarantors.$index.address")<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="guarantor-{{ $index }}-liability" name="guarantors[{{ $index }}][liability_confirmed]" value="1" @checked(old("guarantors.$index.liability_confirmed", $guarantor?->liability_confirmed ?? false)) required>
                        <label class="form-check-label" for="guarantor-{{ $index }}-liability">Liability undertaking confirmed: this guarantor accepts liability if the borrower fails to repay.</label>
                    </div>
                    @error("guarantors.$index.liability_confirmed")<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>
    </div>
@endfor

<div class="d-flex justify-content-between align-items-center mt-4 mb-2">
    <div>
        <h3 class="h5 mb-0">Collateral / assets</h3>
        <p class="text-muted mb-0">Optional. Add assets such as gold or business/land deeds.</p>
    </div>
    <button type="button" class="btn btn-sm btn-outline-primary" id="add-collateral">Add asset</button>
</div>
<div id="collateral-list">
    @foreach ($collateralRows as $index => $item)
        <div class="card mb-3 collateral-row">
            <div class="card-body">
                @if (!empty($item['id']))
                    <input type="hidden" name="collaterals[{{ $index }}][id]" value="{{ $item['id'] }}">
                @endif
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Asset type</label>
                        <input name="collaterals[{{ $index }}][asset_type]" value="{{ $item['asset_type'] ?? '' }}" class="form-control" maxlength="100" placeholder="Gold, business deed, land deed">
                        @error("collaterals.$index.asset_type")<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Estimated value</label>
                        <input type="number" name="collaterals[{{ $index }}][estimated_value]" value="{{ $item['estimated_value'] ?? '' }}" class="form-control" min="0" max="9999999999.99" step="0.01">
                        @error("collaterals.$index.estimated_value")<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Asset document / photo</label>
                        <input type="file" name="collaterals[{{ $index }}][document]" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                        @if (!empty($item['id']) && isset($loan))
                            @php $savedCollateral = $loan->collaterals->firstWhere('id', $item['id']); @endphp
                            @if ($savedCollateral?->document_path)
                                <a class="small" href="{{ route('investments.loans.collateral-document', $savedCollateral) }}">View: {{ $savedCollateral->document_name }}</a>
                            @endif
                        @endif
                        @error("collaterals.$index.document")<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12 mb-2">
                        <label class="form-label">Description</label>
                        <textarea name="collaterals[{{ $index }}][description]" rows="2" class="form-control" maxlength="2000">{{ $item['description'] ?? '' }}</textarea>
                        @error("collaterals.$index.description")<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                </div>
                <button type="button" class="btn btn-sm btn-outline-danger remove-collateral">Remove asset</button>
            </div>
        </div>
    @endforeach
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label for="amount" class="form-label">Loan amount <span class="text-danger">*</span></label>
        <input id="amount" type="number" name="amount" step="0.01" min="0.01" max="9999999999.99" value="{{ old('amount', $loan->amount ?? '') }}" class="form-control @error('amount') is-invalid @enderror" required>
        @error('amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6 mb-3">
        <label for="interest_rate" class="form-label">Interest rate (%) <span class="text-danger">*</span></label>
        <input id="interest_rate" type="number" name="interest_rate" step="0.001" min="0" max="9999.999" value="{{ old('interest_rate', $loan->interest_rate ?? '') }}" class="form-control @error('interest_rate') is-invalid @enderror" required>
        @error('interest_rate')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6 mb-3">
        <label for="duration_months" class="form-label">Duration (months) <span class="text-danger">*</span></label>
        <input id="duration_months" type="number" name="duration_months" min="1" max="6000" step="1" value="{{ old('duration_months', $loan->duration_months ?? '') }}" class="form-control @error('duration_months') is-invalid @enderror" required>
        @error('duration_months')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6 mb-3">
        <label for="start_date" class="form-label">Loan date <span class="text-danger">*</span></label>
        <input id="start_date" type="date" name="start_date" value="{{ old('start_date', isset($loan) ? $loan->start_date->format('Y-m-d') : now()->format('Y-m-d')) }}" class="form-control @error('start_date') is-invalid @enderror" required>
        @error('start_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>

<script>
    (() => {
        const type = document.getElementById('borrower_type');
        const memberSelect = document.getElementById('member_id');
        const clientSelect = document.getElementById('loan_client_id');
        const details = document.getElementById('borrower-details');
        const memberBox = document.getElementById('member-borrower');
        const clientBox = document.getElementById('client-borrower');
        const fields = {
            name: document.getElementById('borrower-name'),
            code: document.getElementById('borrower-code'),
            email: document.getElementById('borrower-email'),
            phone: document.getElementById('borrower-phone'),
            nid: document.getElementById('borrower-nid'),
            address: document.getElementById('borrower-address')
        };

        const refreshBorrower = () => {
            const isMember = type.value === 'member';
            memberBox.classList.toggle('d-none', !isMember);
            clientBox.classList.toggle('d-none', isMember);
            memberSelect.disabled = !isMember;
            clientSelect.disabled = isMember;
            const option = (isMember ? memberSelect : clientSelect).selectedOptions[0];
            const hasSelection = option && option.value;
            details.classList.toggle('d-none', !hasSelection);
            if (!hasSelection) return;
            fields.name.textContent = option.dataset.name || '';
            fields.code.textContent = option.dataset.code ? 'Member ID: ' + option.dataset.code : '';
            fields.email.textContent = option.dataset.email ? 'Email: ' + option.dataset.email : '';
            fields.phone.textContent = option.dataset.phone ? 'Phone: ' + option.dataset.phone : '';
            fields.nid.textContent = option.dataset.nid ? 'NID: ' + option.dataset.nid : '';
            fields.address.textContent = option.dataset.address ? 'Address: ' + option.dataset.address : '';
        };

        type.addEventListener('change', refreshBorrower);
        memberSelect.addEventListener('change', refreshBorrower);
        clientSelect.addEventListener('change', refreshBorrower);
        refreshBorrower();
    })();

    (() => {
        const list = document.getElementById('collateral-list');
        const addButton = document.getElementById('add-collateral');
        let nextIndex = {{ count($collateralRows) }};

        addButton.addEventListener('click', () => {
            const row = document.createElement('div');
            row.className = 'card mb-3 collateral-row';
            row.innerHTML = `
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Asset type</label>
                            <input name="collaterals[${nextIndex}][asset_type]" class="form-control" maxlength="100" placeholder="Gold, business deed, land deed">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Estimated value</label>
                            <input type="number" name="collaterals[${nextIndex}][estimated_value]" class="form-control" min="0" max="9999999999.99" step="0.01">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Asset document / photo</label>
                            <input type="file" name="collaterals[${nextIndex}][document]" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                        </div>
                        <div class="col-12 mb-2">
                            <label class="form-label">Description</label>
                            <textarea name="collaterals[${nextIndex}][description]" rows="2" class="form-control" maxlength="2000"></textarea>
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-danger remove-collateral">Remove asset</button>
                </div>`;
            list.appendChild(row);
            nextIndex++;
        });

        list.addEventListener('click', (event) => {
            if (event.target.closest('.remove-collateral')) {
                event.target.closest('.collateral-row').remove();
            }
        });
    })();
</script>
