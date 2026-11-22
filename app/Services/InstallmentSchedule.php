<?php

namespace App\Services;

use App\Models\Member;
use Illuminate\Support\Carbon;

class InstallmentSchedule
{
    /**
     * Return the next unpaid monthly due dates anchored to the member's joining date.
     *
     * @return list<Carbon>
     */
    public function nextDueDates(Member $member, int $count): array
    {
        $joiningDate = Carbon::parse($member->joining_date)->startOfDay();
        $paidDates = $member->installments()
            ->whereNotNull('deposit_date')
            ->pluck('deposit_date')
            ->map(fn ($date) => Carbon::parse($date)->toDateString())
            ->all();
        $paidDates = array_fill_keys($paidDates, true);

        $dueDates = [];
        for ($monthOffset = 0; count($dueDates) < $count; $monthOffset++) {
            $dueDate = $joiningDate->copy()->addMonthsNoOverflow($monthOffset);
            if (!isset($paidDates[$dueDate->toDateString()])) {
                $dueDates[] = $dueDate;
            }
        }

        return $dueDates;
    }
}
