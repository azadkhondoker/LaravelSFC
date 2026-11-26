@extends('layouts.master')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div><h1>Loans</h1><p class="text-muted mb-0">Loans to members and registered non-member clients.</p></div>
        <a href="{{ route('investments.loans.create') }}" class="btn btn-primary">Enroll loan</a>
    </div>
    @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    <div class="card"><div class="table-responsive">
        <table class="table table-striped mb-0">
            <thead><tr><th>Borrower</th><th>Type</th><th>Amount</th><th>Rate</th><th>Duration</th><th>Loan progress</th><th>Loan date</th><th>Actions</th></tr></thead>
            <tbody>
            @forelse ($loans as $loan)
                <tr>
                    <td>{{ $loan->member?->name ?? $loan->client?->name ?? 'Borrower removed' }}</td>
                    <td>{{ $loan->member_id ? 'Member' : 'Client' }}</td>
                    <td>{{ number_format((float) $loan->amount, 2) }}</td>
                    <td>{{ number_format((float) $loan->interest_rate, 3) }}%</td>
                    <td>{{ $loan->duration_months }} months</td>
                    @php
                        $amountCents = (int) round((float) $loan->amount * 100);
                        $interestCents = (int) round(
                            $amountCents * (float) $loan->interest_rate * (int) $loan->duration_months / 1200
                        );
                        $paidCents = (int) round((float) ($loan->payments_sum_total_amount ?? 0) * 100);
                        $paidPercent = $amountCents + $interestCents > 0
                            ? min(100, (int) round(($paidCents / ($amountCents + $interestCents)) * 100))
                            : 0;
                        $remainingPercent = 100 - $paidPercent;
                    @endphp
                    <td style="min-width: 180px">
                        <div class="d-flex justify-content-between small mb-1">
                            <span>Paid {{ $paidPercent }}%</span>
                            <span>Remaining {{ $remainingPercent }}%</span>
                        </div>
                        <div class="progress" role="progressbar"
                             aria-label="Loan repayment progress"
                             aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $paidPercent }}"
                             style="height: 10px">
                            <div class="progress-bar bg-success" style="width: {{ $paidPercent }}%"></div>
                        </div>
                    </td>
                    <td>{{ $loan->start_date->format('Y-m-d') }}</td>
                    <td class="text-nowrap">
                        <a href="{{ route('investments.loans.schedule', $loan) }}" class="btn btn-sm btn-outline-success">Schedule</a>
                        <a href="{{ route('investments.loans.edit', $loan) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                        <form action="{{ route('investments.loans.destroy', $loan) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this loan record?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center py-4">No loans enrolled yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div></div>
    <div class="mt-3">{{ $loans->links() }}</div>
</div>
@endsection
