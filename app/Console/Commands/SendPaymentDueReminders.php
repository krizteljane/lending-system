<?php

namespace App\Console\Commands;

use App\Mail\PaymentDueReminderMail;
use App\Models\Loan;
use App\Services\LoanAccounting;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendPaymentDueReminders extends Command
{
    protected $signature = 'payments:send-due-reminders';

    protected $description = 'Email borrowers reminders three days before an installment and on its due date.';

    public function handle(LoanAccounting $accounting): int
    {
        if (config('mail.default') === 'log') {
            $this->error('Mail delivery is not configured.');

            return self::FAILURE;
        }

        $today = now()->startOfDay();
        $reminderDateKeys = [
            $today->toDateString() => 'due',
            $today->copy()->addDays(3)->toDateString() => 'three_days',
        ];
        $sent = 0;
        $failed = 0;

        Loan::with(['borrower', 'payments'])->where('status', 'Active')->chunkById(100, function ($loans) use ($accounting, $today, $reminderDateKeys, &$sent, &$failed): void {
            foreach ($loans as $loan) {
                $borrower = $loan->borrower;
                if (! $borrower?->email) {
                    continue;
                }

                foreach ($accounting->summary($loan)['installments'] as $installment) {
                    if ($installment['remaining_due'] <= 0.009) {
                        continue;
                    }

                    $dueDate = Carbon::parse($installment['due_date'])->startOfDay();
                    $reminderType = $reminderDateKeys[$dueDate->toDateString()] ?? null;
                    if (! $reminderType) {
                        continue;
                    }

                    $reminder = $loan->paymentReminders()->firstOrCreate([
                        'installment_month' => (int) $installment['month'],
                        'reminder_type' => $reminderType,
                    ]);
                    if ($reminder->sent_at) {
                        continue;
                    }

                    try {
                        Mail::to($borrower->email)->send(new PaymentDueReminderMail(
                            borrowerName: $borrower->full_name,
                            loanReference: $loan->reference,
                            dueDate: $dueDate->format('F j, Y'),
                            amountDue: number_format((float) $installment['remaining_due'], 2),
                            dueToday: $reminderType === 'due',
                        ));
                        $reminder->forceFill(['sent_at' => now()])->save();
                        $sent++;
                    } catch (Throwable $exception) {
                        report($exception);
                        $reminder->delete();
                        $failed++;
                    }
                }
            }
        });

        $this->info("Sent {$sent} payment reminder(s); {$failed} failed.");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
