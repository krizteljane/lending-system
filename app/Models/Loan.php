<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Loan extends Model
{
    use HasFactory;

    protected $fillable = [
        'borrower_id', 'reference', 'principal', 'monthly_rate', 'calculation_type', 'term_months', 'release_date', 'purpose',
        'co_borrower_name', 'co_borrower_address', 'co_borrower_phone', 'co_borrower_email', 'co_borrower_date_of_birth', 'co_borrower_id_type', 'co_borrower_id_number', 'co_borrower_id_file_path',
        'second_co_borrower_name', 'second_co_borrower_address', 'second_co_borrower_phone', 'second_co_borrower_email', 'second_co_borrower_date_of_birth', 'second_co_borrower_id_type', 'second_co_borrower_id_number', 'second_co_borrower_id_file_path',
        'loan_terms', 'notarial_fee', 'inspection_fee', 'registration_fee', 'documentary_stamp_fee',
        'service_fee', 'additional_charges', 'total_charges', 'net_proceeds', 'total_interest', 'total_payments', 'schedule', 'status',
    ];

    protected function casts(): array
    {
        return [
            'release_date' => 'date',
            'co_borrower_date_of_birth' => 'date',
            'second_co_borrower_date_of_birth' => 'date',
            'schedule' => 'array',
            'additional_charges' => 'array',
            'principal' => 'decimal:2',
            'monthly_rate' => 'decimal:2',
            'total_charges' => 'decimal:2',
            'net_proceeds' => 'decimal:2',
            'total_interest' => 'decimal:2',
            'total_payments' => 'decimal:2',
        ];
    }

    public function borrower(): BelongsTo
    {
        return $this->belongsTo(Borrower::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(LoanPayment::class);
    }
}
