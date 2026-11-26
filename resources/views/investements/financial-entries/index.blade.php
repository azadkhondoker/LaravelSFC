@extends('layouts.master')

@section('content')
<div class="container-fluid">
    @if (isset($type))
        @php
            $reportTitles = [
                'profit' => 'Profit history',
                'interest' => 'Interest income history',
                'loss' => 'Loss history',
                'expense' => 'Expense history',
            ];
            $detailHeadings = [
                'profit' => 'Profit account / source',
                'interest' => 'Client',
                'loss' => 'Loss account / reason',
                'expense' => 'Expense details',
            ];
        @endphp
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="mb-0">{{ $reportTitles[$type] }}</h1>
            <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">Back to dashboard</a>
        </div>
        <form action="{{ route('investments.financial-entries.index') }}" method="GET" class="row g-3 align-items-end mb-4">
            <input type="hidden" name="type" value="{{ $type }}">
            <div class="col-sm-4 col-md-3">
                <label for="report-month" class="form-label">Month</label>
                <select id="report-month" name="month" class="form-select">
                    @foreach (range(1, 12) as $optionMonth)
                        <option value="{{ $optionMonth }}" @selected($month === $optionMonth)>
                            {{ date('F', mktime(0, 0, 0, $optionMonth, 1)) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-4 col-md-3">
                <label for="report-year" class="form-label">Year</label>
                <input id="report-year" type="number" name="year" value="{{ $year }}" min="1900" max="{{ now()->year }}" class="form-control">
            </div>
            <div class="col-sm-4 col-md-3">
                <button type="submit" class="btn btn-primary">Show report</button>
            </div>
        </form>
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <div class="card"><div class="card-body">
                    <h2 class="h6 text-muted">Monthly total — {{ date('F', mktime(0, 0, 0, $month, 1)) }} {{ $year }}</h2>
                    <strong class="fs-4">{{ number_format($monthlyTotal, 2) }}</strong>
                </div></div>
            </div>
            <div class="col-md-6">
                <div class="card"><div class="card-body">
                    <h2 class="h6 text-muted">Yearly total — {{ $year }}</h2>
                    <strong class="fs-4">{{ number_format($yearlyTotal, 2) }}</strong>
                </div></div>
            </div>
        </div>
        <div class="card">
            <div class="table-responsive">
                <table class="table table-striped mb-0">
                    <thead>
                        <tr>
                            <th scope="col">Serial</th>
                            <th scope="col">Date</th>
                            <th scope="col">{{ $detailHeadings[$type] }}</th>
                            <th scope="col" class="text-end">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($records as $record)
                            <tr>
                                <td>{{ $records->firstItem() + $loop->index }}</td>
                                <td>{{ $record['date']->format('Y-m-d') }}</td>
                                <td>{{ $record['details'] }}</td>
                                <td class="text-end">{{ number_format($record['amount'], 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted py-4">No records found.</td></tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr>
                            <th scope="row" colspan="3" class="text-end">Monthly total</th>
                            <th class="text-end">{{ number_format($monthlyTotal, 2) }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        <div class="mt-3">{{ $records->links() }}</div>
    @else
    <div class="mb-4">
        <h1>Profit, loss, expenses &amp; fines</h1>
        <p class="text-muted mb-0">Record non-interest income and fund outflows. Loan interest and land-sale profit are calculated automatically.</p>
    </div>
    @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if ($errors->any())<div class="alert alert-danger">Please review the highlighted fields.</div>@endif

    <div class="row g-3">
        <div class="col-lg-4">
            <div class="card"><div class="card-body">
                <h2 class="h5">Add financial entry</h2>
                <form action="{{ route('investments.financial-entries.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="entry_type" class="form-label">Entry type <span class="text-danger">*</span></label>
                        <select id="entry_type" name="entry_type" class="form-select @error('entry_type') is-invalid @enderror" required>
                            <option value="">Choose type</option>
                            <option value="profit" @selected(old('entry_type') === 'profit')>Other profit</option>
                            <option value="loss" @selected(old('entry_type') === 'loss')>Loss</option>
                            <option value="expense" @selected(old('entry_type') === 'expense')>Expense</option>
                            <option value="fine" @selected(old('entry_type') === 'fine')>Fine</option>
                        </select>
                        @error('entry_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label for="member_id" class="form-label">Member (optional)</label>
                        <select id="member_id" name="member_id" class="form-select @error('member_id') is-invalid @enderror">
                            <option value="">Not member-specific</option>
                            @foreach ($members as $member)
                                <option value="{{ $member->id }}" @selected((string) old('member_id') === (string) $member->id)>
                                    {{ $member->member_code }} — {{ $member->name }}{{ $member->trashed() ? ' (deleted)' : '' }}
                                </option>
                            @endforeach
                        </select>
                        @error('member_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label for="amount" class="form-label">Amount <span class="text-danger">*</span></label>
                        <input id="amount" type="number" name="amount" value="{{ old('amount') }}" min="0.01" max="9999999999.99" step="0.01" class="form-control @error('amount') is-invalid @enderror" required>
                        @error('amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label for="entry_date" class="form-label">Date <span class="text-danger">*</span></label>
                        <input id="entry_date" type="date" name="entry_date" value="{{ old('entry_date', now()->format('Y-m-d')) }}" class="form-control @error('entry_date') is-invalid @enderror" required>
                        @error('entry_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label for="note" class="form-label">Description / reason <span class="text-danger">*</span></label>
                        <textarea id="note" name="note" rows="3" maxlength="2000" class="form-control @error('note') is-invalid @enderror" required>{{ old('note') }}</textarea>
                        @error('note')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <button type="submit" class="btn btn-primary">Save entry</button>
                </form>
            </div></div>
        </div>

        <div class="col-lg-8">
            <div class="card"><div class="table-responsive">
                <table class="table table-striped mb-0">
                    <thead><tr><th>Date</th><th>Type</th><th>Member</th><th>Description</th><th class="text-end">Amount</th><th></th></tr></thead>
                    <tbody>
                    @forelse ($entries as $entry)
                        <tr>
                            <td>{{ $entry->entry_date->format('Y-m-d') }}</td>
                            <td>{{ ucfirst($entry->entry_type) }}</td>
                            <td>{{ $entry->member?->name ?? '—' }}</td>
                            <td>{{ $entry->note }}</td>
                            <td class="text-end">{{ number_format((float) $entry->amount, 2) }}</td>
                            <td>
                                <form action="{{ route('investments.financial-entries.destroy', $entry) }}" method="POST" onsubmit="return confirm('Delete this financial entry? The dashboard totals will be recalculated.')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">No financial entries recorded.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div></div>
            <div class="mt-3">{{ $entries->links() }}</div>
        </div>
    </div>
    @endif
</div>
@endsection
