<?php

namespace App\Http\Controllers;

use App\Models\Installment;
use App\Models\InstallmentPayment;
use App\Models\Member;
use App\Models\posts;
use App\Models\LandInvestment;
use App\Models\LandSale;
use App\Models\Loan;
use App\Models\LoanPayment;
use App\Models\OtherInvestment;
use App\Services\InvestmentSavings;
use App\Services\FinancialSummary;

class DashboardController extends Controller
{
    public function index(InvestmentSavings $investmentSavings, FinancialSummary $financialSummary)
    {
        $data = [
            'recentPosts' => posts::query()
                ->orderByDesc('post_date')
                ->orderByDesc('created_at')
                ->take(5)
                ->get(),
        ];

        if (auth()->user()->can('view members')) {
            $month = now();
            $data += [
                'totalMembers' => Member::count(),
                'activeMembers' => Member::where('status', 'active')->count(),
                'totalSavings' => Installment::sum('amount'),
                'monthlySavings' => Installment::query()
                    ->whereMonth('paid_at', $month->month)
                    ->whereYear('paid_at', $month->year)
                    ->sum('amount'),
                'recentPayments' => InstallmentPayment::with('member')
                    ->latest('paid_at')
                    ->latest('id')
                    ->take(6)
                    ->get(),
                'availableSavings' => $investmentSavings->available(),
                'investedAmount' => $investmentSavings->investedAmount(),
                'loanAmount' => Loan::sum('amount'),
                'landAmount' => LandInvestment::sum('purchase_price') - LandSale::sum('sale_price'),
                'landSalesTotal' => LandSale::sum('sale_price'),
                'monthlyLandSales' => LandSale::query()
                    ->whereMonth('sold_at', $month->month)
                    ->whereYear('sold_at', $month->year)
                    ->sum('sale_price'),
                'otherAmount' => OtherInvestment::sum('amount'),
                'loanInstallmentsCollected' => LoanPayment::query()
                    ->whereMonth('paid_at', $month->month)
                    ->whereYear('paid_at', $month->year)
                    ->sum('total_amount'),
                'loansWithGuarantors' => Loan::whereHas('guarantors', fn ($query) => $query->where('position', 1))
                    ->whereHas('guarantors', fn ($query) => $query->where('position', 2))
                    ->count(),
                'loansWithCollateral' => Loan::has('collaterals')->count(),
                'recentLoanPayments' => LoanPayment::with(['loan.member', 'loan.client'])
                    ->latest('paid_at')
                    ->latest('id')
                    ->take(6)
                    ->get(),
                'financialTotals' => $financialSummary->totals(),
            ];
        }

        return view('dashboard', $data);
    }
}
