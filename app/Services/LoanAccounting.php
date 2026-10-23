<?php

namespace App\Services;

use App\Models\Loan;
use Illuminate\Support\Carbon;

class LoanAccounting
{
    private const LATE_PENALTY_RATE = 0.0125;

    /** @return array{installments: array<int, array<string, mixed>>, returned: float, late_penalty: float, total_due: float, remaining_due: float} */
    public function summary(Loan $loan): array
    {
        $loan->loadMissing('payments');
        $installments = collect($loan->schedule)->map(fn (array $row) => [
            ...$row,
            'paid_amount' => 0.0,
            'remaining_due' => round((float) $row['payment'], 2),
            'settled_at' => null,
        ])->values()->all();

        $payments = $loan->payments->sortBy(fn ($payment) => $payment->paid_at->format('Y-m-d').' '.$payment->created_at->format('H:i:s'));
        foreach ($payments as $payment) {
            $unallocated = (float) $payment->amount;
            foreach ($installments as &$installment) {
                if ($unallocated <= 0.009) {
                    break;
                }

                if ($installment['remaining_due'] <= 0.009) {
                    continue;
                }

                $applied = min($unallocated, $installment['remaining_due']);
                $installment['paid_amount'] = round($installment['paid_amount'] + $applied, 2);
                $installment['remaining_due'] = round(max(0, $installment['remaining_due'] - $applied), 2);
                $unallocated = round(max(0, $unallocated - $applied), 2);
                if ($installment['remaining_due'] <= 0.009 && $installment['settled_at'] === null) {
                    $installment['settled_at'] = $payment->paid_at->toDateString();
                }
            }
            unset($installment);
        }

        $today = now()->startOfDay();
        $penaltyPerLateMonth = round((float) $loan->principal * self::LATE_PENALTY_RATE, 2);
        $latePenalty = 0.0;
        foreach ($installments as &$installment) {
            $dueDate = Carbon::parse($installment['due_date'])->startOfDay();
            $endDate = $installment['settled_at']
                ? Carbon::parse($installment['settled_at'])->startOfDay()
                : $today;
            $graceEnds = $dueDate->copy()->addDays(5);
            $lateMonths = 0;
            if ($installment['remaining_due'] > 0.009 && $today->gte($graceEnds)) {
                $lateDays = $graceEnds->diffInDays($today);
                $lateMonths = 1 + intdiv((int) $lateDays, 30);
            } elseif ($installment['settled_at'] && $endDate->gte($graceEnds)) {
                $lateDays = $graceEnds->diffInDays($endDate);
                $lateMonths = 1 + intdiv((int) $lateDays, 30);
            }

            $installment['late_months'] = $lateMonths;
            $installment['late_penalty'] = round($penaltyPerLateMonth * $lateMonths, 2);
            $latePenalty = round($latePenalty + $installment['late_penalty'], 2);
            $installment['payment_status'] = $installment['remaining_due'] <= 0.009
                ? 'Paid'
                : ($today->gt($dueDate) && $today->gte($graceEnds)
                    ? 'Overdue'
                    : ($installment['paid_amount'] > 0
                        ? 'Partial'
                        : ($today->gte($dueDate) ? 'Due' : 'Upcoming')));
        }
        unset($installment);

        $returned = round((float) $payments->sum('amount'), 2);
        $totalDue = round((float) $loan->total_payments + $latePenalty, 2);

        return [
            'installments' => $installments,
            'returned' => $returned,
            'late_penalty' => $latePenalty,
            'total_due' => $totalDue,
            'remaining_due' => round(max(0, $totalDue - $returned), 2),
        ];
    }
}
