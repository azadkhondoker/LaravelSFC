<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\InstallmentPayment;
use App\Services\InstallmentSchedule;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class InstallmentController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view members')->only('index');
        $this->middleware('permission:edit members')->only('store');
        $this->middleware('permission:view members')->only('receipt');
    }

    public function index(Request $request)
    {
        $members = Member::query()
            ->withSum('installments', 'amount')
            ->withCount('installments')
            ->withMax('installments', 'paid_at')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->input('search');
                $query->where(function ($query) use ($search) {
                    $query->where('member_code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('installment.index', compact('members'));
    }

    public function store(Request $request, Member $member, InstallmentSchedule $schedule)
    {
        $data = $request->validate([
            'total_amount' => 'required|numeric|decimal:0,2|min:0.01|max:9999999999.99',
            'paid_at' => 'required|date',
            'note' => 'nullable|string|max:1000',
        ]);

        $payment = DB::transaction(function () use ($data, $member, $schedule) {
            $lockedMember = Member::query()
                ->lockForUpdate()
                ->findOrFail($member->id);

            if ($lockedMember->installment_amount === null) {
                throw ValidationException::withMessages([
                    'total_amount' => "Set this member's installment amount before recording a deposit.",
                ]);
            }

            $monthlyAmountCents = $this->toMinorUnits($lockedMember->installment_amount);
            $totalAmountCents = $this->toMinorUnits($data['total_amount']);
            if ($totalAmountCents % $monthlyAmountCents !== 0) {
                throw ValidationException::withMessages([
                    'total_amount' => 'Total amount must be a whole multiple of the monthly installment amount ('
                        . number_format((float) $lockedMember->installment_amount, 2, '.', '') . ').',
                ]);
            }

            $installmentCount = intdiv($totalAmountCents, $monthlyAmountCents);
            $dueDates = $schedule->nextDueDates($lockedMember, $installmentCount);

            $payment = $lockedMember->installmentPayments()->create([
                'total_amount' => $this->fromMinorUnits($totalAmountCents),
                'installment_amount' => $lockedMember->installment_amount,
                'installments_count' => $installmentCount,
                'paid_at' => $data['paid_at'],
                'note' => $data['note'] ?? null,
            ]);

            $payment->receipt_no = sprintf(
                'RCPT-%s-%08d',
                $payment->paid_at->format('Ymd'),
                $payment->id,
            );
            $payment->save();

            $payment->installments()->createMany(array_map(
                fn ($dueDate) => [
                    'member_id' => $lockedMember->id,
                    'amount' => $lockedMember->installment_amount,
                    'deposit_date' => $dueDate,
                    'paid_at' => $data['paid_at'],
                    'note' => $data['note'] ?? null,
                ],
                $dueDates,
            ));

            return $payment;
        });

        return redirect()->route('installment-payments.receipt', $payment);
    }

    public function receipt(InstallmentPayment $installmentPayment)
    {
        $installmentPayment->load([
            'member',
            'installments' => fn ($query) => $query->orderBy('deposit_date'),
        ]);

        return view('installment.receipt', compact('installmentPayment'));
    }

    private function toMinorUnits(string $amount): int
    {
        [$whole, $fraction] = array_pad(explode('.', $amount, 2), 2, '');

        return ((int) $whole * 100) + (int) str_pad($fraction, 2, '0');
    }

    private function fromMinorUnits(int $amount): string
    {
        return intdiv($amount, 100) . '.' . str_pad((string) ($amount % 100), 2, '0', STR_PAD_LEFT);
    }
}
