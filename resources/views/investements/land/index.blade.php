@extends('layouts.master')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div><h1>Land investments</h1><p class="text-muted mb-0">Land purchased for the association's assets.</p></div>
        <a href="{{ route('investments.land.create') }}" class="btn btn-primary">Record land purchase</a>
    </div>
    @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if ($errors->has('land'))<div class="alert alert-danger">{{ $errors->first('land') }}</div>@endif
    <div class="card"><div class="table-responsive">
        <table class="table table-striped mb-0">
            <thead><tr><th>Land</th><th>Location</th><th>Purchase price</th><th>Area owned</th><th>Purchase date</th><th>Deed</th><th>Actions</th></tr></thead>
            <tbody>
            @forelse ($lands as $land)
                @php
                    $soldArea = (float) ($land->sales_sum_area_sold ?? 0);
                    $remainingArea = max(0, (float) $land->area - $soldArea);
                @endphp
                <tr>
                    <td>{{ $land->name }}</td><td>{{ $land->location ?: '—' }}</td>
                    <td>{{ number_format((float) $land->purchase_price, 2) }}</td>
                    <td>
                        {{ number_format($remainingArea, 3) }} / {{ number_format((float) $land->area, 3) }} {{ $land->area_unit }}
                        @if ($soldArea > 0)<span class="badge bg-info text-dark">Partially sold</span>@endif
                        @if ($remainingArea <= 0)<span class="badge bg-secondary">Fully sold</span>@endif
                    </td>
                    <td>{{ $land->purchased_at->format('Y-m-d') }}</td>
                    <td>@if ($land->deed_path)<a href="{{ route('investments.land.deed', $land) }}">View PDF</a>@else — @endif</td>
                    <td class="text-nowrap">
                        @if ($remainingArea > 0)
                            <a href="{{ route('investments.land.sell', $land) }}" class="btn btn-sm btn-outline-success">Sell land</a>
                        @endif
                        <a href="{{ route('investments.land.edit', $land) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                        <form action="{{ route('investments.land.destroy', $land) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this land record and its deed?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center py-4">No land purchases recorded.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div></div>
    <div class="mt-3">{{ $lands->links() }}</div>
</div>
@endsection
