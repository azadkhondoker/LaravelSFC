<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use App\Models\LoanCollateral;
use App\Models\LoanClient;
use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\LoanPayment;
use App\Services\InvestmentSavings;
use Illuminate\Validation\ValidationException;
use Throwable;

class LoanController extends Controller
{
    public function index()
    {
        $loans = Loan::with(['member', 'client'])
            ->withSum('payments', 'principal')
            ->withSum('payments', 'interest')
            ->withSum('payments', 'total_amount')
            ->latest()
            ->paginate(10);

        return view('investements.loans.index', compact('loans'));
    }

    public function create()
    {
        $members = Member::where('status', 'active')->orderBy('name')->get();
        $clients = LoanClient::orderBy('name')->get();

        return view('investements.loans.create', compact('members', 'clients'));
    }

    public function store(Request $request, InvestmentSavings $savings)
    {
        $validated = $request->validate(array_merge($this->loanRules(), $this->supportingDetailRules()));
        unset($validated['borrower_type']);
        $amountCents = $savings->toMinorUnits((string) $validated['amount']);
        if ($amountCents > $savings->available()) {
            throw ValidationException::withMessages([
                'amount' => 'Insufficient savings. Available balance: ' . $savings->fromMinorUnits($savings->available()) . '.',
            ]);
        }

        $guarantors = $validated['guarantors'];
        $collaterals = $validated['collaterals'] ?? [];
        unset($validated['guarantors'], $validated['collaterals']);
        $this->validateCollateralRows($collaterals, $request);

        $storedPaths = [];
        $obsoletePaths = [];
        try {
            DB::transaction(function () use ($validated, $guarantors, $collaterals, $request, &$storedPaths, &$obsoletePaths) {
                $loan = Loan::create($validated);
                $this->saveSupportingDetails($loan, $guarantors, $collaterals, $request, $storedPaths, $obsoletePaths);
            });
        } catch (Throwable $exception) {
            foreach ($storedPaths as $path) {
                Storage::disk('local')->delete($path);
            }
            throw $exception;
        }

        return redirect()->route('investments.loans.index')->with('success', 'Loan enrolled successfully.');
    }

    public function edit(Loan $loan)
    {
        $members = Member::where('status', 'active')->orderBy('name')->get();
        $clients = LoanClient::orderBy('name')->get();
        $loan->load(['member', 'client', 'guarantors', 'collaterals']);

        return view('investements.loans.edit', compact('loan', 'members', 'clients'));
    }

    public function update(Request $request, Loan $loan, InvestmentSavings $savings)
    {
        $validated = $request->validate(array_merge($this->loanRules(), $this->supportingDetailRules()));
        unset($validated['borrower_type']);
        $amountCents = $savings->toMinorUnits((string) $validated['amount']);
        if ($amountCents > $savings->availableFor($amountCents, $loan->id)) {
            throw ValidationException::withMessages([
                'amount' => 'Insufficient savings. Available balance: ' . $savings->fromMinorUnits(max(0, $savings->availableFor($amountCents, $loan->id))) . '.',
            ]);
        }
        $guarantors = $validated['guarantors'];
        $collaterals = $validated['collaterals'] ?? [];
        unset($validated['guarantors'], $validated['collaterals']);
        $this->validateCollateralRows($collaterals, $request);

        $storedPaths = [];
        $obsoletePaths = [];
        try {
            DB::transaction(function () use ($loan, $validated, $guarantors, $collaterals, $request, &$storedPaths, &$obsoletePaths) {
                $loan->update($validated);
                $this->saveSupportingDetails($loan, $guarantors, $collaterals, $request, $storedPaths, $obsoletePaths);
            });
        } catch (Throwable $exception) {
            foreach ($storedPaths as $path) {
                Storage::disk('local')->delete($path);
            }
            throw $exception;
        }
        foreach (array_unique($obsoletePaths) as $path) {
            Storage::disk('local')->delete($path);
        }

        return redirect()->route('investments.loans.index')->with('success', 'Loan updated successfully.');
    }

    public function destroy(Loan $loan)
    {
        $documentPaths = $loan->collaterals()->whereNotNull('document_path')->pluck('document_path');
        $loan->delete();
        foreach ($documentPaths as $path) {
            Storage::disk('local')->delete($path);
        }

        return redirect()->route('investments.loans.index')->with('success', 'Loan deleted successfully.');
    }

    public function show(Loan $loan)
    {
        $loan->load(['member', 'client', 'guarantors', 'collaterals']);
        $loan->load(['payments']);
        $paidByMonth = $loan->payments->keyBy('month_number');

        $amountCents = (int) round((float) $loan->amount * 100);
        $duration = (int) $loan->duration_months;
        $interestCents = (int) round(
            $amountCents * (float) $loan->interest_rate * $duration / 1200
        );

        $principalBase = intdiv($amountCents, $duration);
        $principalRemainder = $amountCents % $duration;
        $interestBase = intdiv($interestCents, $duration);
        $interestRemainder = $interestCents % $duration;
        $schedule = [];

        for ($index = 0; $index < $duration; $index++) {
            $principal = $principalBase + ($index < $principalRemainder ? 1 : 0);
            $interest = $interestBase + ($index < $interestRemainder ? 1 : 0);
            $schedule[] = [
                'month' => $index + 1,
                'due_date' => Carbon::parse($loan->start_date)
                    ->startOfDay()
                    ->addMonthsNoOverflow($index + 1),
                'principal' => $principal,
                'interest' => $interest,
                'total' => $principal + $interest,
                'payment' => $paidByMonth->get($index + 1),
            ];
        }

        $paidPrincipalCents = (int) round((float) $loan->payments->sum('principal') * 100);
        $paidInterestCents = (int) round((float) $loan->payments->sum('interest') * 100);
        $paidTotalCents = $paidPrincipalCents + $paidInterestCents;
        $totalPayableCents = $amountCents + $interestCents;
        $paidPercent = $totalPayableCents > 0
            ? min(100, (int) round(($paidTotalCents / $totalPayableCents) * 100))
            : 0;
        $remainingPercent = 100 - $paidPercent;

        return view('investements.loans.show', compact(
            'loan',
            'schedule',
            'amountCents',
            'interestCents',
            'paidPrincipalCents',
            'paidInterestCents',
            'paidTotalCents',
            'paidPercent',
            'remainingPercent',
        ));
    }

    public function pay(Request $request, Loan $loan, int $month)
    {
        $loan->load('member', 'client');
        $request->validate([
            'paid_at' => ['required', 'date'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($month < 1 || $month > $loan->duration_months) {
            abort(404);
        }
        if ($loan->payments()->where('month_number', $month)->exists()) {
            throw ValidationException::withMessages(['month' => 'This installment is already paid.']);
        }

        $amountCents = (int) round((float) $loan->amount * 100);
        $interestCents = (int) round($amountCents * (float) $loan->interest_rate * $loan->duration_months / 1200);
        $principalBase = intdiv($amountCents, $loan->duration_months);
        $principalRemainder = $amountCents % $loan->duration_months;
        $interestBase = intdiv($interestCents, $loan->duration_months);
        $interestRemainder = $interestCents % $loan->duration_months;
        $index = $month - 1;
        $principal = $principalBase + ($index < $principalRemainder ? 1 : 0);
        $interest = $interestBase + ($index < $interestRemainder ? 1 : 0);

        $payment = DB::transaction(function () use ($loan, $month, $principal, $interest, $request) {
            $payment = $loan->payments()->create([
                'month_number' => $month,
                'due_date' => Carbon::parse($loan->start_date)->addMonthsNoOverflow($month),
                'principal' => number_format($principal / 100, 2, '.', ''),
                'interest' => number_format($interest / 100, 2, '.', ''),
                'total_amount' => number_format(($principal + $interest) / 100, 2, '.', ''),
                'paid_at' => $request->input('paid_at'),
                'receipt_no' => 'LOAN-RCPT-' . strtoupper(substr(bin2hex(random_bytes(5)), 0, 10)),
                'note' => $request->input('note'),
            ]);

            $payment->update(['receipt_no' => sprintf('LOAN-RCPT-%08d', $payment->id)]);

            return $payment;
        });

        return redirect()
            ->route('investments.loans.schedule', $loan)
            ->with('success', 'Installment paid successfully.')
            ->with('paid_payment_id', $payment->id);
    }

    public function receipt(LoanPayment $payment)
    {
        $payment->load('loan.member', 'loan.client');

        return view('investements.loans.receipt', compact('payment'));
    }

    public function collateralDocument(LoanCollateral $collateral)
    {
        abort_unless(
            $collateral->document_path && Storage::disk('local')->exists($collateral->document_path),
            404
        );

        return Storage::disk('local')->download(
            $collateral->document_path,
            $collateral->document_name ?: basename($collateral->document_path)
        );
    }

    public function clients()
    {
        $clients = LoanClient::latest()->paginate(10);

        return view('investements.clients.index', compact('clients'));
    }

    public function createClient()
    {
        return view('investements.clients.create');
    }

    public function storeClient(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:100'],
            'phone' => ['nullable', 'string', 'max:20', 'regex:/^\+?[0-9\s().-]+$/'],
            'nid' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:2000'],
        ]);

        LoanClient::create($validated);

        return redirect()->route('investments.clients.index')->with('success', 'Loan client registered successfully.');
    }

    public function editClient(LoanClient $client)
    {
        return view('investements.clients.edit', compact('client'));
    }

    public function updateClient(Request $request, LoanClient $client)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:100'],
            'phone' => ['nullable', 'string', 'max:20', 'regex:/^\+?[0-9\s().-]+$/'],
            'nid' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:2000'],
        ]);

        $client->update($validated);

        return redirect()->route('investments.clients.index')->with('success', 'Loan client updated successfully.');
    }

    public function destroyClient(LoanClient $client)
    {
        $client->delete();

        return redirect()->route('investments.clients.index')->with('success', 'Loan client deleted successfully.');
    }

    private function loanRules(): array
    {
        return [
            'borrower_type' => ['required', Rule::in(['member', 'client'])],
            'member_id' => [
                'exclude_unless:borrower_type,member',
                'required',
                Rule::exists('members', 'id')
                    ->where('status', 'active')
                    ->whereNull('deleted_at'),
            ],
            'loan_client_id' => [
                'exclude_unless:borrower_type,client',
                'required',
                Rule::exists('loan_clients', 'id'),
            ],
            'amount' => ['required', 'numeric', 'decimal:0,2', 'min:0.01', 'max:9999999999.99'],
            'interest_rate' => ['required', 'numeric', 'decimal:0,3', 'min:0', 'max:9999.999'],
            'duration_months' => ['required', 'integer', 'min:1', 'max:6000'],
            'start_date' => ['required', 'date'],
        ];
    }

    private function supportingDetailRules(): array
    {
        return [
            'guarantors' => ['required', 'array', 'size:2'],
            'guarantors.*.name' => ['required', 'string', 'max:100'],
            'guarantors.*.phone' => ['required', 'string', 'max:20', 'regex:/^\+?[0-9\s().-]+$/'],
            'guarantors.*.nid' => ['required', 'string', 'max:30'],
            'guarantors.*.address' => ['required', 'string', 'max:2000'],
            'guarantors.*.liability_confirmed' => ['accepted'],
            'collaterals' => ['nullable', 'array', 'max:20'],
            'collaterals.*.id' => ['nullable', 'integer'],
            'collaterals.*.asset_type' => ['nullable', 'string', 'max:100'],
            'collaterals.*.description' => ['nullable', 'string', 'max:2000'],
            'collaterals.*.estimated_value' => ['nullable', 'numeric', 'decimal:0,2', 'min:0', 'max:9999999999.99'],
            'collaterals.*.document' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
        ];
    }

    private function validateCollateralRows(array $collaterals, Request $request): void
    {
        foreach ($collaterals as $index => $collateral) {
            $hasDetails = filled($collateral['description'] ?? null)
                || filled($collateral['estimated_value'] ?? null)
                || $request->hasFile("collaterals.$index.document");
            if ($hasDetails && blank($collateral['asset_type'] ?? null)) {
                throw ValidationException::withMessages([
                    "collaterals.$index.asset_type" => 'Enter the asset type for each collateral item.',
                ]);
            }
        }
    }

    private function saveSupportingDetails(
        Loan $loan,
        array $guarantors,
        array $collaterals,
        Request $request,
        array &$storedPaths,
        array &$obsoletePaths,
    ): void {
        foreach (array_values($guarantors) as $index => $guarantor) {
            $loan->guarantors()->updateOrCreate(
                ['position' => $index + 1],
                [
                    'name' => $guarantor['name'],
                    'phone' => $guarantor['phone'],
                    'nid' => $guarantor['nid'],
                    'address' => $guarantor['address'],
                    'liability_confirmed' => true,
                ]
            );
        }

        $keptIds = [];
        foreach ($collaterals as $index => $data) {
            $file = $request->file("collaterals.$index.document");
            $existing = null;
            if (!empty($data['id'])) {
                $existing = $loan->collaterals()->find($data['id']);
                if (!$existing) {
                    throw ValidationException::withMessages([
                        "collaterals.$index.id" => 'The selected collateral does not belong to this loan.',
                    ]);
                }
            }

            if (blank($data['asset_type'] ?? null)) {
                continue;
            }

            $attributes = [
                'asset_type' => $data['asset_type'],
                'description' => $data['description'] ?? null,
                'estimated_value' => $data['estimated_value'] ?? null,
            ];
            if ($file) {
                $path = $file->store('loan-collaterals', 'local');
                if (!$path) {
                    throw new \RuntimeException('Unable to store the collateral document.');
                }
                $storedPaths[] = $path;
                if ($existing?->document_path) {
                    $obsoletePaths[] = $existing->document_path;
                }
                $attributes['document_path'] = $path;
                $attributes['document_name'] = substr($file->getClientOriginalName(), 0, 255);
            }

            if ($existing) {
                $existing->update($attributes);
                $keptIds[] = $existing->id;
            } else {
                $created = $loan->collaterals()->create($attributes);
                $keptIds[] = $created->id;
            }
        }

        $removed = $loan->collaterals()->whereNotIn('id', $keptIds)->get();
        foreach ($removed as $collateral) {
            if ($collateral->document_path) {
                $obsoletePaths[] = $collateral->document_path;
            }
            $collateral->delete();
        }
    }
}
