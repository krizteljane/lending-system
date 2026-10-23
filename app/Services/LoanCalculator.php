<?php

namespace App\Services;

use Carbon\CarbonImmutable;

class LoanCalculator
{
    /** @return array{schedule: array<int, array<string, int|float|string>>, total_interest: float, total_payments: float} */
    public function schedule(float $principal, int $termMonths, float $monthlyRate, string $releaseDate, string $calculationType = 'diminishing'): array
    {
        $rate = $monthlyRate / 100;
        $regularPrincipal = round($principal / $termMonths, 2);
        $balance = round($principal, 2);
        $totalInterest = 0.0;
        $totalPayments = 0.0;
        $rows = [];
        $date = CarbonImmutable::parse($releaseDate);

        for ($month = 1; $month <= $termMonths; $month++) {
            $interestBase = $calculationType === 'flat' ? $principal : $balance;
            $interest = round($interestBase * $rate, 2);
            $principalPaid = $month === $termMonths
                ? $balance
                : min($regularPrincipal, $balance);
            $payment = round($principalPaid + $interest, 2);
            $balance = round(max(0, $balance - $principalPaid), 2);
            $totalInterest = round($totalInterest + $interest, 2);
            $totalPayments = round($totalPayments + $payment, 2);

            $rows[] = [
                'month' => $month,
                'due_date' => $date->addMonthsNoOverflow($month)->format('Y-m-d'),
                'payment' => $payment,
                'interest' => $interest,
                'principal' => $principalPaid,
                'balance' => $balance,
            ];
        }

        return ['schedule' => $rows, 'total_interest' => $totalInterest, 'total_payments' => $totalPayments];
    }

}
