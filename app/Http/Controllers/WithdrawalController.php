<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Withdrawal;
use App\Services\WithdrawService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class WithdrawalController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view members')->only('index');
        $this->middleware('permission:edit members')->only(['store', 'approve', 'reject']);
    }

    public function index(Request $request)
    {
        $filters = $request->validate([
            'status' => ['sometimes', Rule::in([
                Withdrawal::PENDING,
                Withdrawal::APPROVED,
                Withdrawal::REJECTED,
                'all',
            ])],
        ]);
        $status = $filters['status'] ?? Withdrawal::PENDING;

        $withdrawals = Withdrawal::with(['member', 'approver'])
            ->when($status !== 'all', fn ($query) => $query->where('status', $status))
            ->latest()
            ->paginate(15)
            ->withQueryString();
        $members = Member::query()->orderBy('name')->get(['id', 'member_code', 'name']);

        return view('withdrawals.index', compact('withdrawals', 'members', 'status'));
    }

    public function store(Request $request, WithdrawService $service)
    {
        $data = $request->validate([
            'member_id' => ['required', 'integer', 'exists:members,id'],
            'amount' => ['required', 'numeric', 'decimal:0,2', 'min:0.01', 'max:999999999999.99'],
            'withdrawal_date' => ['required', 'date', 'after_or_equal:today'],
            'reason' => ['nullable', 'string', 'max:1000'],
        ]);

        $member = Member::query()->findOrFail($data['member_id']);
        $service->request(
            $member,
            (string) $data['amount'],
            $data['withdrawal_date'],
            $data['reason'] ?? null,
        );

        return redirect()->route('withdrawals.index')
            ->with('success', 'Withdrawal request recorded and is waiting for approval.');
    }

    public function approve(Request $request, Withdrawal $withdrawal, WithdrawService $service)
    {
        $data = $request->validate([
            'admin_note' => ['nullable', 'string', 'max:2000'],
        ]);

        $service->approve($withdrawal, $request->user()->id, $data['admin_note'] ?? null);

        return back()->with('success', "Withdrawal {$withdrawal->transaction_id} approved.");
    }

    public function reject(Request $request, Withdrawal $withdrawal, WithdrawService $service)
    {
        $data = $request->validate([
            'admin_note' => ['nullable', 'string', 'max:2000'],
        ]);

        $service->reject($withdrawal, $request->user()->id, $data['admin_note'] ?? null);

        return back()->with('success', "Withdrawal {$withdrawal->transaction_id} rejected.");
    }
}
