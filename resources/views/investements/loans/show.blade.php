@extends('layouts.master')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1>Loan installment schedule</h1>
            <p class="text-muted mb-0">{{ $loan->member?->name ?? $loan->client?->name ?? 'Borrower removed' }}</p>
        </div>
        <a href="{{ route('investments.loans.index') }}" class="btn btn-light border">Back to loans</a>
    </div>
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
            @if (session('paid_payment_id'))
                <a class="alert-link ms-2" href="{{ route('investments.loans.receipt', session('paid_payment_id')) }}">View receipt</a>
            @endif
        </div>
    @endif

    <div class="card mb-3"><div class="card-body">
        <div class="row">
            <div class="col-md-3"><span class="text-muted d-block">Loan amount</span><strong>{{ number_format($amountCents / 100, 2) }}</strong></div>
            <div class="col-md-3"><span class="text-muted d-block">Interest rate</span><strong>{{ number_format((float) $loan->interest_rate, 3) }}%</strong></div>
            <div class="col-md-3"><span class="text-muted d-block">Total interest</span><strong>{{ number_format($interestCents / 100, 2) }}</strong></div>
            <div class="col-md-3"><span class="text-muted d-block">Total payable</span><strong>{{ number_format(($amountCents + $interestCents) / 100, 2) }}</strong></div>
        </div>
        <div class="mt-3">
            <div class="d-flex justify-content-between small mb-1">
                <strong>Paid {{ $paidPercent }}%</strong>
                <strong>Remaining {{ $remainingPercent }}%</strong>
            </div>
            <div class="progress" role="progressbar"
                 aria-label="Loan repayment progress"
                 aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $paidPercent }}"
                 style="height: 14px">
                <div class="progress-bar bg-success" style="width: {{ $paidPercent }}%"></div>
            </div>
            <div class="d-flex justify-content-between mt-2 small text-muted">
                <span>Paid: {{ number_format($paidTotalCents / 100, 2) }}</span>
                <span>Remaining: {{ number_format(max(0, $amountCents + $interestCents - $paidTotalCents) / 100, 2) }}</span>
            </div>
        </div>
    </div></div>

    <div class="row g-3 mb-3">
        @foreach ($loan->guarantors as $guarantor)
            <div class="col-lg-6">
                <div class="card h-100"><div class="card-body">
                    <h2 class="h5">Guarantor {{ $guarantor->position }}</h2>
                    <div><strong>{{ $guarantor->name }}</strong></div>
                    <div>Phone: {{ $guarantor->phone }}</div>
                    <div>NID: {{ $guarantor->nid }}</div>
                    <div>Address: {{ $guarantor->address }}</div>
                    <span class="badge bg-success mt-2">Liability undertaking confirmed</span>
                </div></div>
            </div>
        @endforeach
    </div>

    <div class="card mb-3"><div class="card-body">
        <h2 class="h5">Collateral / assets</h2>
        @forelse ($loan->collaterals as $collateral)
            <div class="border rounded p-3 mb-2">
                <div class="d-flex justify-content-between">
                    <strong>{{ $collateral->asset_type }}</strong>
                    @if ($collateral->estimated_value !== null)
                        <span>Estimated value: {{ number_format((float) $collateral->estimated_value, 2) }}</span>
                    @endif
                </div>
                @if ($collateral->description)<p class="mb-1">{{ $collateral->description }}</p>@endif
                @if ($collateral->document_path)
                    <a href="{{ route('investments.loans.collateral-document', $collateral) }}">View document: {{ $collateral->document_name }}</a>
                @endif
            </div>
        @empty
            <p class="text-muted mb-0">No collateral recorded for this loan.</p>
        @endforelse
    </div></div>

    <div class="card"><div class="table-responsive">
        <table class="table table-striped mb-0">
            <thead><tr><th>Month</th><th>Due date</th><th>Principal</th><th>Interest</th><th>Installment</th><th>Status / payment</th></tr></thead>
            <tbody>
            @foreach ($schedule as $item)
                <tr>
                    <td>{{ $item['month'] }}</td>
                    <td>{{ $item['due_date']->format('Y-m-d') }}</td>
                    <td>{{ number_format($item['principal'] / 100, 2) }}</td>
                    <td>{{ number_format($item['interest'] / 100, 2) }}</td>
                    <td><strong>{{ number_format($item['total'] / 100, 2) }}</strong></td>
                    <td>
                        @if ($item['payment'])
                            <span class="badge bg-success">Paid</span>
                            <a class="btn btn-sm btn-outline-secondary" href="{{ route('investments.loans.receipt', $item['payment']) }}">Receipt</a>
                        @else
                            <form action="{{ route('investments.loans.pay', [$loan, $item['month']]) }}" method="POST" class="d-flex gap-2 align-items-center">
                                @csrf
                                <input type="date" name="paid_at" value="{{ now()->format('Y-m-d') }}" class="form-control form-control-sm" style="width: 145px" required>
                                <button type="submit" class="btn btn-sm btn-primary">Pay</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div></div>
</div>
@endsection
