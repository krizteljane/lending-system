<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('borrower_id')->constrained()->cascadeOnDelete();
            $table->string('reference')->unique();
            $table->decimal('principal', 12, 2);
            $table->decimal('monthly_rate', 5, 2);
            $table->unsignedSmallInteger('term_months');
            $table->date('release_date');
            $table->string('purpose')->nullable();
            $table->string('co_borrower_name')->nullable();
            $table->decimal('notarial_fee', 10, 2)->default(0);
            $table->decimal('inspection_fee', 10, 2)->default(0);
            $table->decimal('registration_fee', 10, 2)->default(0);
            $table->decimal('documentary_stamp_fee', 10, 2)->default(0);
            $table->decimal('service_fee', 10, 2)->default(400);
            $table->decimal('total_charges', 10, 2);
            $table->decimal('net_proceeds', 12, 2);
            $table->decimal('total_interest', 12, 2);
            $table->decimal('total_payments', 12, 2);
            $table->json('schedule');
            $table->string('status')->default('Active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loans');
    }
};
