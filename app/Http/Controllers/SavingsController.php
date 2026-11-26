<?php

namespace App\Http\Controllers;

use App\Models\Installment;
use App\Models\Member;
use Illuminate\Http\Request;
use App\Services\InvestmentSavings;

class SavingsController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view members');
    }

    public function index(Request $request, InvestmentSavings $fund)
    {
        $members = Member::withTrashed()
            ->withSum('installments', 'amount')
            ->withCount('installments')
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

        $totalSavings = Installment::sum('amount');
        $totalDeposits = Installment::count();
        $membersWithDeposits = Member::withTrashed()
            ->whereHas('installments')
            ->count();
        $rawSavings = $fund->rawSavings();
        $investedAmount = $fund->investedAmount();
        $availableSavings = $fund->available();
        $totalSavings = $fund->fromMinorUnits($rawSavings);

        return view('savings.index', compact(
            'members',
            'totalSavings',
            'totalDeposits',
            'membersWithDeposits',
            'rawSavings',
            'investedAmount',
            'availableSavings',
        ));
    }
}
