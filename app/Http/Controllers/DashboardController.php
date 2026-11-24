<?php

namespace App\Http\Controllers;

use App\Models\Installment;
use App\Models\InstallmentPayment;
use App\Models\Member;
use App\Models\posts;

class DashboardController extends Controller
{
    public function index()
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
            ];
        }

        return view('dashboard', $data);
    }
}
