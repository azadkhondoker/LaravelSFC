@extends('layouts.master')

@section('content')
<div class="container-fluid members-page dashboard-page">
    <div class="members-page-header dashboard-page-header">
        <h1>Welcome {{ auth()->user()->name }}</h1>
    </div>

    @can('view members')
        <div class="dashboard-summary-grid">
        <a href="{{ route('members.index') }}" class="card dashboard-stat-card">
            <span class="dashboard-stat-icon dashboard-stat-icon-blue"><i class="ti-user" aria-hidden="true"></i></span>
            <span class="dashboard-stat-copy">
                <span class="dashboard-stat-label">Total members</span>
                <strong>{{ number_format($totalMembers) }}</strong>
                <small>Registered members</small>
            </span>
        </a>
        <a href="{{ route('members.index') }}" class="card dashboard-stat-card">
            <span class="dashboard-stat-icon dashboard-stat-icon-green"><i class="ti-check-box" aria-hidden="true"></i></span>
            <span class="dashboard-stat-copy">
                <span class="dashboard-stat-label">Active members</span>
                <strong>{{ number_format($activeMembers) }}</strong>
                <small>Currently active</small>
            </span>
        </a>
        <a href="{{ route('savings.index') }}" class="card dashboard-stat-card">
            <span class="dashboard-stat-icon dashboard-stat-icon-purple"><i class="ti-wallet" aria-hidden="true"></i></span>
            <span class="dashboard-stat-copy">
                <span class="dashboard-stat-label">Total savings</span>
                <strong>{{ number_format((float) $totalSavings, 2) }}</strong>
                <small>All recorded deposits</small>
            </span>
        </a>
        <a href="{{ route('savings.index') }}" class="card dashboard-stat-card">
            <span class="dashboard-stat-icon dashboard-stat-icon-amber"><i class="ti-calendar" aria-hidden="true"></i></span>
            <span class="dashboard-stat-copy">
                <span class="dashboard-stat-label">Collected this month</span>
                <strong>{{ number_format((float) $monthlySavings, 2) }}</strong>
                <small>{{ now()->format('F Y') }}</small>
            </span>
        </a>
        </div>
    @endcan

    <div class="dashboard-content-grid @cannot('view members') dashboard-content-grid-notices-only @endcannot">
        @can('view members')
            <section class="card dashboard-panel">
                <div class="dashboard-panel-header">
                    <div>
                        <h2>Recent deposits</h2>
                        <p>Latest installment payments recorded</p>
                    </div>
                    <a href="{{ route('installments.index') }}" class="dashboard-panel-link">View installments <i class="ti-arrow-right" aria-hidden="true"></i></a>
                </div>
                <div class="table-responsive">
                    <table class="table members-table dashboard-table">
                        <thead>
                            <tr>
                                <th scope="col">Member</th>
                                <th scope="col">Date</th>
                                <th scope="col">Receipt</th>
                                <th scope="col" class="text-end">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($recentPayments as $payment)
                                <tr>
                                    <td>
                                        <span class="member-name">{{ $payment->member?->name ?? 'Deleted member' }}</span>
                                        <span class="member-code">{{ $payment->member?->member_code ?? 'Member record unavailable' }}</span>
                                    </td>
                                    <td>{{ $payment->paid_at->format('M d, Y') }}</td>
                                    <td>
                                        <a class="dashboard-panel-link" href="{{ route('installment-payments.receipt', $payment) }}">
                                            {{ $payment->receipt_no ?: 'Payment #' . $payment->id }}
                                        </a>
                                    </td>
                                    <td class="text-end dashboard-amount">{{ number_format((float) $payment->total_amount, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="members-empty">
                                        <strong>No deposits recorded yet</strong>
                                        <span class="text-muted">Member installment payments will appear here.</span>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        @endcan

        <section class="card dashboard-panel dashboard-notices-panel">
            <div class="dashboard-panel-header">
                <div>
                    <h2>Recent notices</h2>
                    <p>Latest updates shared with members</p>
                </div>
                <a href="{{ route('posts.index') }}" class="dashboard-panel-link">All notices <i class="ti-arrow-right" aria-hidden="true"></i></a>
            </div>
            <div class="dashboard-notice-list">
                @forelse ($recentPosts as $post)
                    <article class="dashboard-notice">
                        <span class="dashboard-notice-icon"><i class="ti-announcement" aria-hidden="true"></i></span>
                        <div>
                            <h3>{{ $post->title }}</h3>
                            <p>{{ \Illuminate\Support\Str::limit($post->content, 110) }}</p>
                            <time datetime="{{ ($post->post_date ?? $post->created_at)->toDateString() }}">
                                {{ ($post->post_date ?? $post->created_at)->format('M d, Y') }}
                            </time>
                        </div>
                    </article>
                @empty
                    <div class="dashboard-empty">
                        <strong>No notices yet</strong>
                        <span>Create a notice to share updates with members.</span>
                        <a href="{{ route('posts.create') }}" class="btn btn-sm btn-primary">Create notice</a>
                    </div>
                @endforelse
            </div>
        </section>
    </div>
</div>
@endsection
