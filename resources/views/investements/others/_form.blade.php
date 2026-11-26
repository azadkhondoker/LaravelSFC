@php($selectedCategory = (string) old('category_id', $investment->investment_category_id ?? ''))
<div class="row">
    <div class="col-md-6 mb-3">
        <label for="category_id" class="form-label">Investment category <span class="text-danger">*</span></label>
        <select id="category_id" name="category_id" class="form-select @error('category_id') is-invalid @enderror">
            <option value="">Add a new category</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected($selectedCategory === (string) $category->id)>{{ $category->name }}</option>
            @endforeach
        </select>
        @error('category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6 mb-3" id="new-category-wrapper">
        <label for="new_category" class="form-label">New category name <span class="text-danger">*</span></label>
        <input id="new_category" name="new_category" value="{{ old('new_category') }}" maxlength="100" class="form-control @error('new_category') is-invalid @enderror" placeholder="e.g. Gold, Silver, Business share">
        @error('new_category')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6 mb-3">
        <label for="name" class="form-label">Investment description <span class="text-danger">*</span></label>
        <input id="name" name="name" value="{{ old('name', $investment->name ?? '') }}" maxlength="150" class="form-control @error('name') is-invalid @enderror" required>
        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6 mb-3">
        <label for="amount" class="form-label">Amount invested <span class="text-danger">*</span></label>
        <input id="amount" type="number" name="amount" value="{{ old('amount', $investment->amount ?? '') }}" min="0.01" step="0.01" class="form-control @error('amount') is-invalid @enderror" required>
        @error('amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6 mb-3">
        <label for="invested_at" class="form-label">Investment date <span class="text-danger">*</span></label>
        <input id="invested_at" type="date" name="invested_at" value="{{ old('invested_at', isset($investment) ? $investment->invested_at->format('Y-m-d') : now()->format('Y-m-d')) }}" class="form-control @error('invested_at') is-invalid @enderror" required>
        @error('invested_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-12 mb-3">
        <label for="details" class="form-label">Details</label>
        <textarea id="details" name="details" rows="3" class="form-control @error('details') is-invalid @enderror">{{ old('details', $investment->details ?? '') }}</textarea>
        @error('details')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6 mb-3">
        <label for="receipt" class="form-label">Purchase challan/receipt (PDF or JPG, max 10 MB)</label>
        <input id="receipt" type="file" name="receipt" accept="application/pdf,.pdf,image/jpeg,.jpg,.jpeg" class="form-control @error('receipt') is-invalid @enderror">
        @error('receipt')<div class="invalid-feedback">{{ $message }}</div>@enderror
        @isset($investment)
            @if ($investment->receipt_path)
                <a class="d-inline-block mt-2" href="{{ route('investments.others.receipt', $investment) }}">View current challan/receipt</a>
            @endif
        @endisset
    </div>
</div>
<script>
    (() => {
        const category = document.getElementById('category_id');
        const wrapper = document.getElementById('new-category-wrapper');
        const input = document.getElementById('new_category');
        const refresh = () => {
            const adding = category.value === '';
            wrapper.classList.toggle('d-none', !adding);
            input.disabled = !adding;
        };
        category.addEventListener('change', refresh);
        refresh();
    })();
</script>
