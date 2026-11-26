<?php

namespace App\Http\Controllers;

use App\Models\FinancialEntry;
use App\Models\LandSale;
use App\Models\LoanPayment;
use App\Models\Member;
use App\Services\FinancialSummary;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\Rule;

class FinancialEntryController extends Controller
{
    public function index(Request $request, FinancialSummary $financialSummary)
    {
        $validated = $request->validate([
            'type' => ['nullable', Rule::in(['profit', 'interest', 'loss', 'expense'])],
            'month' => ['nullable', 'integer', 'between:1,12'],
            'year' => ['nullable', 'integer', 'min:1900', 'max:'.now()->year],
        ]);
        $type = $validated['type'] ?? null;

        if ($type !== null) {
            $month = (int) ($validated['month'] ?? now()->month);
            $year = (int) ($validated['year'] ?? now()->year);
            $records = collect();

            if ($type === 'interest') {
                $records = LoanPayment::query()
                    ->with(['loan.member', 'loan.client'])
                    ->where('interest', '>', 0)
                    ->get()
                    ->map(fn (LoanPayment $payment) => [
                        'id' => $payment->id,
                        'date' => $payment->paid_at,
                        'amount' => (float) $payment->interest,
                        'details' => $payment->loan?->client?->name
                            ?? $payment->loan?->member?->name
                            ?? 'Client not recorded',
                    ]);
            } else {
                $records = FinancialEntry::query()
                    ->with('member')
                    ->where('entry_type', $type)
                    ->get()
                    ->map(fn (FinancialEntry $entry) => [
                        'id' => $entry->id,
                        'date' => $entry->entry_date,
                        'amount' => (float) $entry->amount,
                        'details' => $entry->note.($entry->member ? ' — '.$entry->member->name : ''),
                    ]);

                if ($type === 'profit') {
                    $landSaleProfits = LandSale::with('land')->get()
                        ->map(fn (LandSale $sale) => [
                            'id' => $sale->id,
                            'date' => $sale->sold_at,
                            'amount' => $financialSummary->landSaleProfitFor($sale) / 100,
                            'details' => 'Land sale: '.($sale->land?->name ?? 'Land')
                                .($sale->buyer_name ? ' — '.$sale->buyer_name : ''),
                        ])
                        ->filter(fn (array $record) => $record['amount'] != 0);

                    $records = $records->concat($landSaleProfits);
                }
            }

            $records = $records
                ->sort(function (array $left, array $right): int {
                    $dateOrder = $right['date']->toDateString() <=> $left['date']->toDateString();

                    return $dateOrder !== 0 ? $dateOrder : ($right['id'] <=> $left['id']);
                })
                ->values();
            $yearRecords = $records->filter(
                fn (array $record) => (int) $record['date']->format('Y') === $year
            );
            $yearlyTotal = $yearRecords->sum('amount');
            $records = $yearRecords
                ->filter(fn (array $record) => (int) $record['date']->format('n') === $month)
                ->values();
            $monthlyTotal = $records->sum('amount');
            $perPage = 15;
            $page = LengthAwarePaginator::resolveCurrentPage();
            $records = new LengthAwarePaginator(
                $records->forPage($page, $perPage)->values(),
                $records->count(),
                $perPage,
                $page,
                ['path' => LengthAwarePaginator::resolveCurrentPath(), 'query' => $request->query()]
            );

            return view('investements.financial-entries.index', compact(
                'type',
                'records',
                'month',
                'year',
                'monthlyTotal',
                'yearlyTotal'
            ));
        }

        $entries = FinancialEntry::with('member')->latest('entry_date')->latest('id')->paginate(15);
        $members = Member::withTrashed()->orderBy('name')->get();

        return view('investements.financial-entries.index', compact('entries', 'members'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'entry_type' => ['required', Rule::in(['profit', 'loss', 'expense', 'fine'])],
            'member_id' => ['nullable', 'integer', Rule::exists('members', 'id')],
            'amount' => ['required', 'numeric', 'decimal:0,2', 'min:0.01', 'max:9999999999.99'],
            'entry_date' => ['required', 'date'],
            'note' => ['required', 'string', 'max:2000'],
        ]);

        FinancialEntry::create($validated);

        return redirect()->route('investments.financial-entries.index')->with('success', 'Financial entry recorded successfully.');
    }

    public function destroy(FinancialEntry $entry)
    {
        $entry->delete();

        return redirect()->route('investments.financial-entries.index')->with('success', 'Financial entry deleted successfully.');
    }
}
