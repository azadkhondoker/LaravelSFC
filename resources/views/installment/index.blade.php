@extends('layouts.master')

@section('content')
<div class="container-fluid members-page">
    <div class="members-page-header">
        <div>
            <h1>Installments</h1>
            <p>Every member appears here automatically. Open a member profile to record a deposit.</p>
        </div>
    </div>

    <div class="card members-card">
        <div class="card-header">
            <div>
                <h2>Member installment accounts</h2>
                <small class="text-muted">{{ $members->total() }} {{ \Illuminate\Support\Str::plural('member', $members->total()) }}</small>
            </div>
            <form action="{{ route('installments.index') }}" method="GET" class="members-search" role="search">
                <label for="installment-search" class="visually-hidden">Search members</label>
                <input
                    id="installment-search"
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
                    <a href="{{ route('installments.index') }}" class="btn btn-light border">Clear</a>
                @endif
            </form>
        </div>

        <div class="table-responsive">
            <table class="table members-table">
                <thead>
                    <tr>
                        <th scope="col">Member</th>
                        <th scope="col">Joined</th>
                        <th scope="col">Deposits</th>
                        <th scope="col">Total deposited</th>
                        <th scope="col">Last deposit</th>
                        <th scope="col" class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($members as $member)
                        <tr>
                            <td>
                                <span class="member-name">{{ $member->name }}</span>
                                <span class="member-code">{{ $member->member_code }}</span>
                            </td>
                            <td>{{ $member->joining_date->format('M d, Y') }}</td>
                            <td>{{ $member->installments_count }}</td>
                            <td class="installment-total">{{ number_format((float) ($member->installments_sum_amount ?? 0), 2) }}</td>
                            <td>
                                @if ($member->installments_max_paid_at)
                                    {{ \Illuminate\Support\Carbon::parse($member->installments_max_paid_at)->format('M d, Y') }}
                                @else
                                    <span class="text-muted">No deposits yet</span>
                                @endif
                            </td>
                            <td>
                                <div class="member-actions">
                                    <a
                                        href="{{ route('members.show', $member) }}"
                                        class="btn btn-light border text-primary"
                                        aria-label="View installment profile for {{ $member->name }}"
                                        title="View profile and record deposit"
                                    >
                                        <i class="ti-eye" aria-hidden="true"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="members-empty">
                                <strong>{{ request()->filled('search') ? 'No matching members found' : 'No members yet' }}</strong>
                                <span class="text-muted">
                                    {{ request()->filled('search') ? 'Try another name, email address, or member code.' : 'Members will appear here after they are added.' }}
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
