@extends('layouts.master')

@section('content')
<div class="container-fluid receipt-page">
    <div class="receipt-actions">
        <a
            href="{{ $installmentPayment->member->trashed() ? route('installments.index') : route('members.show', $installmentPayment->member) }}"
            class="btn btn-light border"
        >
            <i class="ti-arrow-left me-1" aria-hidden="true"></i> Back
        </a>
        <button type="button" class="btn btn-primary" onclick="window.print()">
            <i class="ti-printer me-1" aria-hidden="true"></i> Print receipt
        </button>
    </div>

    <div class="card member-page-card receipt-card">
        <div class="card-header receipt-header">
            <div>
                <span class="receipt-eyebrow">Installment payment receipt</span>
                <h1>Payment received</h1>
                <span class="receipt-number">{{ $installmentPayment->receipt_no }}</span>
            </div>
            <span class="receipt-paid-badge">Paid</span>
        </div>
        <div class="card-body">
            <div class="receipt-details-grid">
                <div>
                    <span class="receipt-label">Member</span>
                    <strong>{{ $installmentPayment->member->name }}</strong>
                    <span class="receipt-subtext">{{ $installmentPayment->member->member_code }}</span>
                </div>
                <div>
                    <span class="receipt-label">Payment date</span>
                    <strong>{{ $installmentPayment->paid_at->format('M d, Y') }}</strong>
                </div>
                <div>
                    <span class="receipt-label">Monthly installment</span>
                    <strong>{{ number_format((float) $installmentPayment->installment_amount, 2) }}</strong>
                </div>
                <div>
                    <span class="receipt-label">Installments covered</span>
                    <strong>{{ $installmentPayment->installments_count }}</strong>
                </div>
                @if ($installmentPayment->member->trashed())
                    <div>
                        <span class="receipt-label">Member status</span>
                        <strong>Deleted member</strong>
                    </div>
                @endif
            </div>

            <div class="table-responsive receipt-table-wrap">
                <table class="table members-table receipt-table">
                    <thead>
                        <tr>
                            <th scope="col">Installment due date</th>
                            <th scope="col">Installment amount</th>
                            <th scope="col">Paid date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($installmentPayment->installments as $installment)
                            <tr>
                                <td>{{ $installment->deposit_date->format('M d, Y') }}</td>
                                <td>{{ number_format((float) $installment->amount, 2) }}</td>
                                <td>{{ $installment->paid_at->format('M d, Y') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th scope="row">Total paid</th>
                            <th>{{ number_format((float) $installmentPayment->total_amount, 2) }}</th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
            </div>

            @if ($installmentPayment->note)
                <div class="receipt-note">
                    <span class="receipt-label">Note</span>
                    <p>{{ $installmentPayment->note }}</p>
                </div>
            @endif

            <p class="receipt-footnote">This is a computer-generated receipt for the installment payment above.</p>
        </div>
    </div>
</div>
@endsection
