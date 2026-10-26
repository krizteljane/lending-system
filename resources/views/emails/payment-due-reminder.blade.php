<p>Hello {{ $borrowerName }},</p>

@if ($dueToday)
    <p>This is a reminder that your loan installment is due today.</p>
@else
    <p>This is a reminder that an installment for your loan is due in three days.</p>
@endif

<p><strong>Loan reference:</strong> {{ $loanReference }}<br>
<strong>Due date:</strong> {{ $dueDate }}<br>
<strong>Remaining installment amount:</strong> ₱{{ $amountDue }}</p>

<p>If you have already paid, please contact the cooperative so your payment can be recorded.</p>
<p>San Luis Agri-Ventures Agriculture Cooperative</p>
