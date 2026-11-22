@extends('layouts.master')

@section('content')
<div class="container-fluid members-page">
    <div class="member-page-header">
        <div>
            <h1>{{ $member->name }}</h1>
            <p>Member profile and installment deposit history.</p>
        </div>
        <a href="{{ route('installments.index') }}" class="btn btn-light border">
            <i class="ti-arrow-left me-1" aria-hidden="true"></i> Back to installments
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger" role="alert">
            <strong>Please review the form.</strong> Some details need your attention.
        </div>
    @endif

    <div class="installment-summary-grid">
        <div class="card installment-summary-card">
            <span class="installment-summary-label">Member code</span>
            <strong>{{ $member->member_code }}</strong>
        </div>
        <div class="card installment-summary-card">
            <span class="installment-summary-label">Fixed installment amount</span>
            <strong>
                {{ $member->installment_amount !== null ? number_format((float) $member->installment_amount, 2) : 'Not set' }}
            </strong>
        </div>
        <div class="card installment-summary-card">
            <span class="installment-summary-label">Installments deposited</span>
            <strong>{{ $member->installments_count }}</strong>
        </div>
        <div class="card installment-summary-card">
            <span class="installment-summary-label">Total deposited</span>
            <strong>{{ number_format((float) ($member->installments_sum_amount ?? 0), 2) }}</strong>
        </div>
    </div>

    <div class="card member-page-card installment-member-card">
        <div class="card-header">
            <i class="ti-user me-2" aria-hidden="true"></i> Member information
        </div>
        <div class="card-body">
            <div class="member-profile-grid">
                <div>
                    <span class="member-profile-label">Full name</span>
                    <strong>{{ $member->name }}</strong>
                </div>
                <div>
                    <span class="member-profile-label">Fixed installment amount</span>
                    <strong>
                        {{ $member->installment_amount !== null ? number_format((float) $member->installment_amount, 2) : 'Not set' }}
                    </strong>
                </div>
                <div>
                    <span class="member-profile-label">Email</span>
                    <strong>{{ $member->email }}</strong>
                </div>
                <div>
                    <span class="member-profile-label">Phone</span>
                    <strong>{{ $member->phone ?: '—' }}</strong>
                </div>
                <div>
                    <span class="member-profile-label">Joined</span>
                    <strong>{{ $member->joining_date->format('M d, Y') }}</strong>
                </div>
                <div>
                    <span class="member-profile-label">Status</span>
                    <strong>{{ ucfirst($member->status) }}</strong>
                </div>
            </div>
        </div>
    </div>

    @if ($member->installment_amount === null)
        <div class="alert alert-warning installment-member-card" role="alert">
            Set this member's installment amount from the member edit form before recording deposits.
            @can('edit members')
                <a href="{{ route('members.edit', $member) }}" class="alert-link">Edit member</a>
            @endcan
        </div>
    @elseif (auth()->user()->can('edit members'))
        <div class="card member-page-card installment-member-card">
            <div class="card-header">
                <i class="ti-plus me-2" aria-hidden="true"></i> Record an installment deposit
            </div>
            <div class="card-body">
                <form action="{{ route('members.installments.store', $member) }}" method="POST">
                    @csrf
                    <div class="installment-deposit-grid">
                        <div class="member-field">
                            <label for="monthly_amount">Monthly installment amount</label>
                            <input
                                id="monthly_amount"
                                type="text"
                                value="{{ number_format((float) $member->installment_amount, 2, '.', '') }}"
                                class="form-control"
                                readonly
                            >
                            <small class="form-text">The fixed amount recorded for each month.</small>
                        </div>
                        <div class="member-field">
                            <label for="total_amount">Total amount <span class="text-danger">*</span></label>
                            <input
                                id="total_amount"
                                type="number"
                                name="total_amount"
                                value="{{ old('total_amount') }}"
                                data-installment-amount="{{ $member->installment_amount }}"
                                class="form-control @error('total_amount') is-invalid @enderror"
                                step="any"
                                inputmode="decimal"
                                placeholder="Enter total payment"
                                required
                            >
                            @error('total_amount')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="member-field">
                            <label for="next_due_date">Next deposit date (due)</label>
                            <input
                                id="next_due_date"
                                type="date"
                                value="{{ $nextDueDate->format('Y-m-d') }}"
                                class="form-control"
                                readonly
                            >
                            <small class="form-text">Each next installment will be scheduled monthly from this date.</small>
                        </div>
                        <div class="member-field">
                            <label for="paid_at">Paid date <span class="text-danger">*</span></label>
                            <input
                                id="paid_at"
                                type="date"
                                name="paid_at"
                                value="{{ old('paid_at', now()->format('Y-m-d')) }}"
                                class="form-control @error('paid_at') is-invalid @enderror"
                                required
                            >
                            @error('paid_at')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="member-field installment-note-field">
                            <label for="note">Note</label>
                            <input
                                id="note"
                                type="text"
                                name="note"
                                value="{{ old('note') }}"
                                class="form-control @error('note') is-invalid @enderror"
                                maxlength="1000"
                                placeholder="Optional reference or comment"
                            >
                            @error('note')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="form-text installment-total-preview" id="installment-total-preview" aria-live="polite">
                        The total must be an exact multiple of the monthly installment amount.
                    </div>
                    <div class="member-form-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="ti-check me-1" aria-hidden="true"></i> Save deposit
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <div class="card members-card installment-member-card">
        <div class="card-header">
            <div>
                <h2>Deposit history</h2>
                <small class="text-muted">{{ $member->installments_count }} {{ \Illuminate\Support\Str::plural('deposit', $member->installments_count) }}</small>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table members-table">
                <thead>
                    <tr>
                        <th scope="col">Installment due date</th>
                        <th scope="col">Installment amount</th>
                        <th scope="col">Note</th>
                        <th scope="col">Paid date</th>
                        <th scope="col">Receipt</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($installments as $installment)
                        <tr>
                            <td>{{ $installment->deposit_date?->format('M d, Y') ?? '—' }}</td>
                            <td class="installment-total">{{ number_format((float) $installment->amount, 2) }}</td>
                            <td>{{ $installment->note ?: '—' }}</td>
                            <td>{{ $installment->paid_at->format('M d, Y') }}</td>
                            <td>
                                @if ($installment->payment)
                                    <a href="{{ route('installment-payments.receipt', $installment->payment) }}">
                                        {{ $installment->payment->receipt_no }}
                                    </a>
                                @else
                                    <span class="text-muted">Legacy</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="members-empty">
                                <strong>No deposits recorded</strong>
                                <span class="text-muted">Deposits for this member will appear here.</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($installments->hasPages())
            <div class="members-pagination">
                {{ $installments->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
</div>

<div
    class="modal fade post-details-modal installment-error-modal"
    id="installment-amount-error"
    tabindex="-1"
    aria-labelledby="installment-amount-error-title"
    aria-describedby="installment-amount-error-message"
    aria-hidden="true"
>
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <span class="post-modal-eyebrow">Installment amount</span>
                    <h2 class="modal-title" id="installment-amount-error-title">Input the right amount</h2>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="post-modal-content" id="installment-amount-error-message"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Okay</button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.querySelector('form[action*="/installments"]');
        const totalInput = form ? form.querySelector('[name="total_amount"]') : null;
        const preview = document.getElementById('installment-total-preview');
        const errorModalElement = document.getElementById('installment-amount-error');
        const errorMessage = document.getElementById('installment-amount-error-message');

        function showAmountError(message) {
            errorMessage.textContent = message;
            bootstrap.Modal.getOrCreateInstance(errorModalElement).show();
        }

        if (form && totalInput) {
            const monthlyAmountCents = Math.round(Number(totalInput.dataset.installmentAmount) * 100);

            function installmentCount() {
                if (!totalInput.value) {
                    return null;
                }

                const totalAmountCents = Math.round(Number(totalInput.value) * 100);
                if (!Number.isFinite(totalAmountCents)
                    || !Number.isSafeInteger(totalAmountCents)
                    || totalAmountCents <= 0
                    || totalAmountCents % monthlyAmountCents !== 0) {
                    return null;
                }

                return totalAmountCents / monthlyAmountCents;
            }

            totalInput.addEventListener('input', function () {
                const count = installmentCount();
                preview.textContent = count
                    ? 'This payment will cover ' + count + ' monthly installment(s).'
                    : 'The total must be an exact multiple of '
                        + (monthlyAmountCents / 100).toFixed(2) + '.';
            });

            form.addEventListener('submit', function (event) {
                const count = installmentCount();

                if (!count) {
                    event.preventDefault();
                    showAmountError(
                        'Total amount must be a whole multiple of the monthly installment amount ('
                        + (monthlyAmountCents / 100).toFixed(2) + ').'
                    );
                }
            });
        }

        @if ($errors->has('total_amount'))
            showAmountError(@json($errors->first('total_amount')));
        @endif
    });
</script>
@endsection
