@extends('layouts.master')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1>Investments</h1>
            <p class="text-muted mb-0">Manage loans, land, and other assets.</p>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-md-6 col-xl-3">
            <a href="{{ route('investments.loans.index') }}" class="card text-decoration-none h-100">
                <div class="card-body"><h5>Loans</h5><p class="mb-1">{{ $summary['loans'] }} enrolled</p><strong>{{ number_format($summary['loan_amount'], 2) }}</strong></div>
            </a>
        </div>
        <div class="col-md-6 col-xl-3">
            <a href="{{ route('investments.land.index') }}" class="card text-decoration-none h-100">
                <div class="card-body"><h5>Land</h5><p class="mb-1">Net investment</p><strong>{{ number_format($summary['land_amount'], 2) }}</strong><p class="mb-0 mt-2">Sale proceeds: {{ number_format($summary['land_sales'], 2) }}</p></div>
            </a>
        </div>
        <div class="col-md-6 col-xl-3">
            <a href="{{ route('investments.others.index') }}" class="card text-decoration-none h-100">
                <div class="card-body"><h5>Other investments</h5><p class="mb-1">{{ $summary['categories'] }} categories</p><strong>{{ number_format($summary['other_amount'], 2) }}</strong></div>
            </a>
        </div>
        <div class="col-md-6 col-xl-3">
            <a href="{{ route('investments.clients.index') }}" class="card text-decoration-none h-100">
                <div class="card-body"><h5>Loan clients</h5><p class="mb-1">Register non-members before enrollment</p><strong>Manage clients</strong></div>
            </a>
        </div>
        <div class="col-md-6 col-xl-3">
            <a href="{{ route('investments.financial-entries.index') }}" class="card text-decoration-none h-100">
                <div class="card-body"><h5>Profit, loss &amp; expenses</h5><p class="mb-1">Record other profit, loss, expenses and fines</p><strong>Manage accounts</strong></div>
            </a>
        </div>
    </div>
</div>
@endsection
