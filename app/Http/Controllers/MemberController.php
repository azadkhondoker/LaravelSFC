<?php

namespace App\Http\Controllers;

use App\Http\Requests\MemberRequest;
use App\Models\Member;
use App\Services\InstallmentSchedule;
use Illuminate\Http\Request;


class MemberController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view members')->only(['index', 'show', 'deleted']);
        $this->middleware('permission:create members')->only(['create', 'store']);
        $this->middleware('permission:edit members')->only(['edit', 'update']);
        $this->middleware('permission:delete members')->only(['destroy', 'restore']);
    }

    public function index(Request $request)
    {
        $members = Member::query();

        if ($request->has('search')) {
            $search = $request->input('search');
            $members->where(function ($query) use ($search) {
                $query->where('member_code', 'like', "%$search%")
                    ->orWhere('name', 'like', "%$search%")
                    ->orWhere('email', 'like', "%$search%");
            });
        }

        $members = $members->paginate(10);

        return view('members.index', compact('members'));
    }

    public function deleted(Request $request)
    {
        $members = Member::onlyTrashed()
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->input('search');
                $query->where(function ($query) use ($search) {
                    $query->where('member_code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('deleted_at')
            ->paginate(10)
            ->withQueryString();

        return view('members.deleted', compact('members'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('members.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(MemberRequest $request)
    {
        member::create($request->validated());
        return redirect()->route('members.index')->with('success', 'Member created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Member $member, InstallmentSchedule $schedule)
    {
        $member->loadSum('installments', 'amount')
            ->loadCount('installments');
        $nextDueDate = $member->installment_amount !== null
            ? $schedule->nextDueDates($member, 1)[0]
            : null;

        $installments = $member->installments()
            ->orderByDesc('paid_at')
            ->orderByDesc('deposit_date')
            ->orderByDesc('id')
            ->paginate(10);

        return view('members.show', compact('member', 'installments', 'nextDueDate'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Member $member)
    {
        return view('members.edit', compact('member'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(MemberRequest $request, Member $member)
    {
        $member->update($request->validated());
        return redirect()->route('members.index')->with('success', 'Member updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Member $member)
    {
        $member->delete();
        return redirect()->route('members.index')->with('success', 'Member deleted successfully.');
    }

    public function restore(Member $member)
    {
        $member->restore();

        return redirect()
            ->route('members.deleted')
            ->with('success', 'Member restored successfully.');
    }
}
