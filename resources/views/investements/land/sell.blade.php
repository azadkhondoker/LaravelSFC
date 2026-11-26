@extends('layouts.master')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1>Sell land</h1>
            <p class="text-muted mb-0">{{ $land->name }} — {{ $land->location ?: 'No location recorded' }}</p>
        </div>
        <a href="{{ route('investments.land.index') }}" class="btn btn-light border">Back to land</a>
    </div>

    @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if ($errors->any())<div class="alert alert-danger">Please review the sale details below.</div>@endif

    <div class="row g-3">
        <div class="col-lg-5">
            <div class="card"><div class="card-body">
                <h2 class="h5">Record a land sale</h2>
                <p>Remaining area: <strong>{{ number_format($areaRemaining / 1000, 3) }} {{ $land->area_unit }}</strong></p>
                @if ($areaRemaining > 0)
                    <form action="{{ route('investments.land.sales.store', $land) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label for="area_sold" class="form-label">Area sold ({{ $land->area_unit }}) <span class="text-danger">*</span></label>
                            <input id="area_sold" type="number" name="area_sold" value="{{ old('area_sold') }}" min="0.001" max="{{ number_format($areaRemaining / 1000, 3, '.', '') }}" step="0.001" class="form-control @error('area_sold') is-invalid @enderror" required>
                            @error('area_sold')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label for="sale_price" class="form-label">Sale price <span class="text-danger">*</span></label>
                            <input id="sale_price" type="number" name="sale_price" value="{{ old('sale_price') }}" min="0.01" max="9999999999.99" step="0.01" class="form-control @error('sale_price') is-invalid @enderror" required>
                            @error('sale_price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label for="buyer_name" class="form-label">Buyer name <span class="text-danger">*</span></label>
                            <input id="buyer_name" name="buyer_name" value="{{ old('buyer_name') }}" maxlength="150" class="form-control @error('buyer_name') is-invalid @enderror" required>
                            @error('buyer_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label for="buyer_phone" class="form-label">Buyer phone</label>
                            <input id="buyer_phone" name="buyer_phone" value="{{ old('buyer_phone') }}" maxlength="30" class="form-control @error('buyer_phone') is-invalid @enderror">
                            @error('buyer_phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label for="sold_at" class="form-label">Sale date <span class="text-danger">*</span></label>
                            <input id="sold_at" type="date" name="sold_at" value="{{ old('sold_at', now()->format('Y-m-d')) }}" class="form-control @error('sold_at') is-invalid @enderror" required>
                            @error('sold_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label for="deed" class="form-label">Sale deed (PDF/image, max 10 MB)</label>
                            <input id="deed" type="file" name="deed" accept=".pdf,.jpg,.jpeg,.png" class="form-control @error('deed') is-invalid @enderror">
                            @error('deed')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <button type="submit" class="btn btn-success">Record sale</button>
                    </form>
                @else
                    <div class="alert alert-secondary mb-0">This land parcel has been fully sold.</div>
                @endif
            </div></div>
        </div>

        <div class="col-lg-7">
            <div class="card"><div class="card-body">
                <h2 class="h5">Sale history</h2>
                <div class="table-responsive">
                    <table class="table table-striped mb-0">
                        <thead><tr><th>Date</th><th>Buyer</th><th>Area sold</th><th>Sale price</th><th>Deed</th></tr></thead>
                        <tbody>
                        @forelse ($land->sales as $sale)
                            <tr>
                                <td>{{ $sale->sold_at->format('Y-m-d') }}</td>
                                <td>{{ $sale->buyer_name }}{{ $sale->buyer_phone ? ' — ' . $sale->buyer_phone : '' }}</td>
                                <td>{{ number_format((float) $sale->area_sold, 3) }} {{ $land->area_unit }}</td>
                                <td>{{ number_format((float) $sale->sale_price, 2) }}</td>
                                <td>@if ($sale->deed_path)<a href="{{ route('investments.land.sales.deed', $sale) }}">View deed</a>@else — @endif</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-4">No sales recorded yet.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div></div>
        </div>
    </div>
</div>
@endsection
