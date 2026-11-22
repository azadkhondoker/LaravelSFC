@extends('layouts.master')

@section('content')
<div class="container-fluid members-page savings-page">
    <div class="members-page-header">
        <div>
            <h1>Savings</h1>
            <p>Track total member savings from recorded installment deposits.</p>
        </div>
    </div>

    <div class="savings-summary-grid">
        <div class="card installment-summary-card savings-total-card">
            <span class="installment-summary-label">Total savings</span>
            <strong>{{ number_format((float) $totalSavings, 2) }}</strong>
            <small>Combined member installment deposits</small>
        </div>
        <div class="card installment-summary-card">
            <span class="installment-summary-label">Members with deposits</span>
            <strong>{{ number_format($membersWithDeposits) }}</strong>
            <small>Includes archived members</small>
        </div>
        <div class="card installment-summary-card">
            <span class="installment-summary-label">Total deposits</span>
            <strong>{{ number_format($totalDeposits) }}</strong>
            <small>Installment payments recorded</small>
        </div>
    </div>

    <div class="alert alert-info savings-investment-note" role="status">
        Total savings currently reflects member deposits only. Investment categories and their profit/loss are not tracked yet.
    </div>

    <div class="card members-card">
        <div class="card-header">
            <div>
                <h2>Member raw savings</h2>
                <small class="text-muted">{{ $members->total() }} {{ \Illuminate\Support\Str::plural('member', $members->total()) }}</small>
            </div>
            <form action="{{ route('savings.index') }}" method="GET" class="members-search" role="search">
                <label for="savings-member-search" class="visually-hidden">Search members</label>
                <input
                    id="savings-member-search"
                    type="search"
                    name="search"
                    value="{{ request('search') }}"
                    class="form-control"
                    placeholder="Search name, email or member code"
                >
                <button type="submit" class="btn btn-primary" aria-label="Search">
                    <i class="ti-search" aria-hidden="true"></i>
                </button>
                @if (request()->filled('search'))
                    <a href="{{ route('savings.index') }}" class="btn btn-light border">Clear</a>
                @endif
            </form>
        </div>

        <div class="table-responsive">
            <table class="table members-table">
                <thead>
                    <tr>
                        <th scope="col">Member</th>
                        <th scope="col">Member status</th>
                        <th scope="col">Installments deposited</th>
                        <th scope="col" class="text-end">Raw savings</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($members as $member)
                        <tr>
                            <td>
                                <span class="member-name">{{ $member->name }}</span>
                                <span class="member-code">{{ $member->member_code }}</span>
                            </td>
                            <td>
                                @if ($member->trashed())
                                    <span class="member-status member-status-deleted">Deleted</span>
                                @elseif ($member->status === 'active')
                                    <span class="member-status member-status-active">Active</span>
                                @else
                                    <span class="member-status member-status-inactive">Inactive</span>
                                @endif
                            </td>
                            <td>{{ number_format($member->installments_count) }}</td>
                            <td class="installment-total text-end">
                                {{ number_format((float) ($member->installments_sum_amount ?? 0), 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="members-empty">
                                <strong>{{ request()->filled('search') ? 'No matching members found' : 'No members yet' }}</strong>
                                <span class="text-muted">
                                    {{ request()->filled('search') ? 'Try another name, email address, or member code.' : 'Member savings will appear here after installment deposits are recorded.' }}
                                </span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($members->hasPages())
            <div class="members-pagination">
                {{ $members->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
</div>
@endsection
