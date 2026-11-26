<?php

namespace App\Http\Controllers;

use App\Models\InvestmentCategory;
use App\Models\LandInvestment;
use App\Models\LandSale;
use App\Models\Loan;
use App\Models\OtherInvestment;

class InvestmentController extends Controller
{
    public function index()
    {
        $summary = [
            'loans' => Loan::count(),
            'loan_amount' => Loan::sum('amount'),
            'land_amount' => LandInvestment::sum('purchase_price') - LandSale::sum('sale_price'),
            'land_sales' => LandSale::sum('sale_price'),
            'other_amount' => OtherInvestment::sum('amount'),
            'categories' => InvestmentCategory::count(),
        ];

        return view('investements.index', compact('summary'));
    }
}
