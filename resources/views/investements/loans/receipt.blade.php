@extends('layouts.master')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div><h1>Loan installment receipt</h1><p class="text-muted mb-0">Receipt no: {{ $payment->receipt_no }}</p></div>
        <a href="{{ route('investments.loans.schedule', $payment->loan) }}" class="btn btn-light border">Back to schedule</a>
    </div>
    <div class="card"><div class="card-body">
        <div class="row mb-3">
            <div class="col-md-6"><strong>Borrower</strong><div>{{ $payment->loan->member?->name ?? $payment->loan->client?->name ?? 'Borrower removed' }}</div></div>
            <div class="col-md-3"><strong>Month</strong><div>{{ $payment->month_number }}</div></div>
            <div class="col-md-3"><strong>Paid date</strong><div>{{ $payment->paid_at->format('Y-m-d') }}</div></div>
        </div>
        <table class="table">
            <tbody>
                <tr><th>Due date</th><td>{{ $payment->due_date->format('Y-m-d') }}</td></tr>
                <tr><th>Principal</th><td>{{ number_format((float) $payment->principal, 2) }}</td></tr>
                <tr><th>Interest</th><td>{{ number_format((float) $payment->interest, 2) }}</td></tr>
                <tr><th>Total paid</th><td><strong>{{ number_format((float) $payment->total_amount, 2) }}</strong></td></tr>
            </tbody>
        </table>
        @if ($payment->note)<div class="alert alert-light border"><strong>Note:</strong> {{ $payment->note }}</div>@endif
        <button type="button" class="btn btn-primary" onclick="window.print()">Print receipt</button>
    </div></div>
</div>
@endsection
