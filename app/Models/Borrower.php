<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Borrower extends Model
{
    use HasFactory;

    protected $fillable = ['full_name', 'address', 'phone', 'email', 'date_of_birth', 'id_type', 'id_number', 'id_file_path'];

    protected function casts(): array
    {
        return ['date_of_birth' => 'date'];
    }

    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class);
    }
}
