<p>Hello {{ $borrowerName }},</p>

<p>This is a receipt for your payment to San Luis Agri-Ventures Agriculture Cooperative.</p>

<ul>
    <li>Loan reference: {{ $loanReference }}</li>
    <li>Amount received: PHP {{ $amount }}</li>
    <li>Payment date: {{ $paymentDate }}</li>
</ul>

<p>Please keep this email for your records. Contact the cooperative if you believe any payment detail is incorrect.</p>

<p>San Luis Agri-Ventures Agriculture Cooperative</p>
