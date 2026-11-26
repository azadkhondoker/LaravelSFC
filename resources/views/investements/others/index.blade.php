@extends('layouts.master')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div><h1>Other investments</h1><p class="text-muted mb-0">Track investments such as business shares, gold, and silver.</p></div>
        <a href="{{ route('investments.others.create') }}" class="btn btn-primary">Add investment</a>
    </div>
    @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    <div class="card"><div class="table-responsive">
        <table class="table table-striped mb-0">
            <thead><tr><th>Category</th><th>Investment</th><th>Amount</th><th>Date</th><th>Details</th><th>Challan/Receipt</th><th>Actions</th></tr></thead>
            <tbody>
            @forelse ($investments as $investment)
                <tr>
                    <td>{{ $investment->category->name }}</td><td>{{ $investment->name }}</td>
                    <td>{{ number_format((float) $investment->amount, 2) }}</td>
                    <td>{{ $investment->invested_at->format('Y-m-d') }}</td><td>{{ $investment->details ?: '—' }}</td>
                    <td>@if ($investment->receipt_path)<a href="{{ route('investments.others.receipt', $investment) }}">View file</a>@else — @endif</td>
                    <td class="text-nowrap">
                        <a href="{{ route('investments.others.edit', $investment) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                        <form action="{{ route('investments.others.destroy', $investment) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this investment record?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center py-4">No other investments recorded.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div></div>
    <div class="mt-3">{{ $investments->links() }}</div>
</div>
@endsection
