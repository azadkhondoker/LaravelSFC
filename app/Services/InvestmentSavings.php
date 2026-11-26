<?php

namespace App\Services;

use App\Models\Installment;
use App\Models\LandInvestment;
use App\Models\LandSale;
use App\Models\Loan;
use App\Models\OtherInvestment;

class InvestmentSavings
{
    public function rawSavings(): int
    {
        return $this->toMinorUnits((string) Installment::sum('amount'));
    }

    public function investedAmount(): int
    {
        $loans = 0;
        foreach (Loan::withSum('payments', 'principal')->get() as $loan) {
            $loanAmount = $this->toMinorUnits((string) $loan->amount);
            $paidPrincipal = $this->toMinorUnits((string) ($loan->payments_sum_principal ?? '0'));
            $loans += max(0, $loanAmount - $paidPrincipal);
        }
        $landPurchases = $this->toMinorUnits((string) LandInvestment::sum('purchase_price'));
        $landSales = $this->toMinorUnits((string) LandSale::sum('sale_price'));
        $land = $landPurchases - $landSales;
        $others = $this->toMinorUnits((string) OtherInvestment::sum('amount'));

        return $loans + $land + $others;
    }

    public function available(): int
    {
        return $this->rawSavings() - $this->investedAmount();
    }

    public function availableFor(int $minorUnits, int $excludeLoanId = 0): int
    {
        $invested = $this->investedAmount();
        if ($excludeLoanId) {
            $loan = Loan::find($excludeLoanId);
            if ($loan) {
                $invested -= $this->toMinorUnits((string) $loan->amount);
            }
        }

        return $this->rawSavings() - $invested;
    }

    public function availableAfterRemovingInvestments(int $minorUnitsToRestore): int
    {
        return $this->rawSavings() - $this->investedAmount() + $minorUnitsToRestore;
    }

    public function toMinorUnits(string $amount): int
    {
        [$whole, $fraction] = array_pad(explode('.', $amount, 2), 2, '');
        $fraction = substr(str_pad($fraction, 2, '0'), 0, 2);

        return ((int) $whole * 100) + (int) $fraction;
    }

    public function fromMinorUnits(int $amount): string
    {
        return intdiv($amount, 100) . '.' . str_pad((string) ($amount % 100), 2, '0', STR_PAD_LEFT);
    }
}
