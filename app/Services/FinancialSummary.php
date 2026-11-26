<?php

namespace App\Services;

use App\Models\FinancialEntry;
use App\Models\Installment;
use App\Models\LandSale;
use App\Models\LoanPayment;

class FinancialSummary
{
    public function __construct(private readonly InvestmentSavings $amounts)
    {
    }

    /**
     * All returned amounts are integer cents.
     *
     * @return array<string, int>
     */
    public function totals(): array
    {
        $savings = $this->amounts->toMinorUnits((string) Installment::sum('amount'));
        $interest = $this->amounts->toMinorUnits((string) LoanPayment::sum('interest'));
        $otherProfit = $this->amounts->toMinorUnits((string) FinancialEntry::where('entry_type', 'profit')->sum('amount'));
        $profit = $this->landSaleProfit() + $otherProfit;
        $fine = $this->amounts->toMinorUnits((string) FinancialEntry::where('entry_type', 'fine')->sum('amount'));
        $loss = $this->amounts->toMinorUnits((string) FinancialEntry::where('entry_type', 'loss')->sum('amount'));
        $expense = $this->amounts->toMinorUnits((string) FinancialEntry::where('entry_type', 'expense')->sum('amount'));

        return [
            'savings' => $savings,
            'profit' => $profit,
            'interest' => $interest,
            'fine' => $fine,
            'loss' => $loss,
            'expense' => $expense,
            'total' => $savings + $profit + $interest + $fine - $loss - $expense,
        ];
    }

    private function landSaleProfit(): int
    {
        $profit = 0;
        foreach (LandSale::with('land')->get() as $sale) {
            $profit += $this->landSaleProfitFor($sale);
        }

        return $profit;
    }

    public function landSaleProfitFor(LandSale $sale): int
    {
        if (!$sale->land || (float) $sale->land->area <= 0) {
            return 0;
        }

        $purchaseCents = $this->amounts->toMinorUnits((string) $sale->land->purchase_price);
        $areaMilliunits = $this->areaToMilliunits((string) $sale->land->area);
        $soldMilliunits = $this->areaToMilliunits((string) $sale->area_sold);
        $costBasis = (int) round($purchaseCents * $soldMilliunits / $areaMilliunits);
        $saleCents = $this->amounts->toMinorUnits((string) $sale->sale_price);

        return $saleCents - $costBasis;
    }

    private function areaToMilliunits(string $area): int
    {
        [$whole, $fraction] = array_pad(explode('.', $area, 2), 2, '');

        return ((int) $whole * 1000) + (int) substr(str_pad($fraction, 3, '0'), 0, 3);
    }
}
