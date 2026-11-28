@extends('layouts.master')

@section('content')
<div class="container-fluid members-page">
    <div class="members-page-header">
        <div class="content-head">
            <h1>Withdrawals</h1>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success" role="status">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>
    @endif

    @can('edit members')
        <div class="card members-card mb-4">
            <div class="card-header">
                <div>
                    <h2>Record withdrawal request</h2>
                    <small class="text-muted">The request is deducted from savings only after approval.</small>
                </div>
            </div>
            <div class="card-body">
                <form action="{{ route('withdrawals.store') }}" method="POST" class="row g-3">
                    @csrf
                    <div class="col-md-4">
                        <label for="withdrawal-member" class="form-label">Member</label>
                        <select id="withdrawal-member" name="member_id" class="form-select" required>
                            <option value="">Select a member</option>
                            @foreach ($members as $member)
                                <option value="{{ $member->id }}" @selected(old('member_id') == $member->id)>
                                    {{ $member->member_code }} — {{ $member->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('member_id') <div class="text-danger small">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-2">
                        <label for="withdrawal-amount" class="form-label">Amount</label>
                        <input
                            id="withdrawal-amount"
                            name="amount"
                            type="number"
                            min="0.01"
                            max="999999999999.99"
                            step="0.01"
                            value="{{ old('amount') }}"
                            class="form-control"
                            required
                        >
                        @error('amount') <div class="text-danger small">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-3">
                        <label for="withdrawal-date" class="form-label">Withdrawal date</label>
                        <input
                            id="withdrawal-date"
                            name="withdrawal_date"
                            type="date"
                            min="{{ now()->toDateString() }}"
                            value="{{ old('withdrawal_date', now()->toDateString()) }}"
                            class="form-control"
                            required
                        >
                        @error('withdrawal_date') <div class="text-danger small">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-3">
                        <label for="withdrawal-reason" class="form-label">Reason <span class="text-muted">(optional)</span></label>
                        <input
                            id="withdrawal-reason"
                            name="reason"
                            value="{{ old('reason') }}"
                            maxlength="1000"
                            class="form-control"
                        >
                        @error('reason') <div class="text-danger small">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">Submit request</button>
                    </div>
                </form>
            </div>
        </div>
    @endcan

    <div class="card members-card">
        <div class="card-header">
            <div>
                <h2>Withdrawal history</h2>
                <small class="text-muted">{{ $withdrawals->total() }} requests</small>
            </div>
            <div class="d-flex flex-wrap gap-2">
                @foreach (['pending', 'approved', 'rejected', 'all'] as $filter)
                    <a
                        href="{{ route('withdrawals.index', ['status' => $filter]) }}"
                        class="btn btn-sm {{ $status === $filter ? 'btn-primary' : 'btn-light border' }}"
                        @if ($status === $filter) aria-current="page" @endif
                    >{{ ucfirst($filter) }}</a>
                @endforeach
            </div>
        </div>

        <div class="table-responsive">
            <table class="table members-table align-middle">
                <thead>
                    <tr>
                        <th scope="col">Transaction</th>
                        <th scope="col">Member</th>
                        <th scope="col">Date</th>
                        <th scope="col" class="text-end">Amount</th>
                        <th scope="col">Status</th>
                        <th scope="col">Reason / admin note</th>
                        <th scope="col">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($withdrawals as $withdrawal)
                        <tr>
                            <td>{{ $withdrawal->transaction_id }}</td>
                            <td>
                                <span class="member-name">{{ $withdrawal->member->name ?? 'Member unavailable' }}</span>
                                @if ($withdrawal->member)
                                    <span class="member-code">{{ $withdrawal->member->member_code }}</span>
                                @endif
                            </td>
                            <td>{{ $withdrawal->withdrawal_date->format('M d, Y') }}</td>
                            <td class="text-end">{{ number_format((float) $withdrawal->amount, 2) }}</td>
                            <td>
                                @php
                                    $statusClass = match ($withdrawal->status) {
                                        'approved' => 'bg-success',
                                        'rejected' => 'bg-danger',
                                        default => 'bg-warning text-dark',
                                    };
                                @endphp
                                <span class="badge {{ $statusClass }}">{{ ucfirst($withdrawal->status) }}</span>
                            </td>
                            <td>
                                @if ($withdrawal->reason)
                                    <div>{{ $withdrawal->reason }}</div>
                                @endif
                                @if ($withdrawal->admin_note)
                                    <small class="text-muted">Admin: {{ $withdrawal->admin_note }}</small>
                                @endif
                                @if (!$withdrawal->reason && !$withdrawal->admin_note)
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                @can('edit members')
                                    @if ($withdrawal->status === 'pending')
                                        <form method="POST" class="d-flex flex-wrap gap-1">
                                            @csrf
                                            <input
                                                name="admin_note"
                                                class="form-control form-control-sm"
                                                placeholder="Optional note"
                                                aria-label="Admin note for {{ $withdrawal->transaction_id }}"
                                            >
                                            <button
                                                type="submit"
                                                formaction="{{ route('withdrawals.approve', $withdrawal) }}"
                                                class="btn btn-sm btn-success"
                                            >Approve</button>
                                            <button
                                                type="submit"
                                                formaction="{{ route('withdrawals.reject', $withdrawal) }}"
                                                class="btn btn-sm btn-outline-danger"
                                            >Reject</button>
                                        </form>
                                    @else
                                        <span class="text-muted">{{ $withdrawal->approver->name ?? '—' }}</span>
                                    @endif
                                @else
                                    <span class="text-muted">{{ $withdrawal->approver->name ?? '—' }}</span>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="members-empty">
                                <strong>No withdrawal requests found</strong>
                                <span class="text-muted">Requests will appear here after they are recorded.</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($withdrawals->hasPages())
            <div class="members-pagination">
                {{ $withdrawals->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
</div>
@endsection
