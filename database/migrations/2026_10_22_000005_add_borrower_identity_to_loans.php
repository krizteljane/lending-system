<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('borrowers', function (Blueprint $table) {
            $table->date('date_of_birth')->nullable();
            $table->string('id_type')->nullable();
            $table->string('id_number', 100)->nullable();
            $table->string('id_file_path')->nullable();
        });

        Schema::table('loans', function (Blueprint $table) {
            $table->string('co_borrower_address')->nullable();
            $table->string('co_borrower_phone', 40)->nullable();
            $table->string('co_borrower_email')->nullable();
            $table->date('co_borrower_date_of_birth')->nullable();
            $table->string('co_borrower_id_type')->nullable();
            $table->string('co_borrower_id_number', 100)->nullable();
            $table->string('co_borrower_id_file_path')->nullable();
            $table->string('second_co_borrower_name')->nullable();
            $table->string('second_co_borrower_address')->nullable();
            $table->string('second_co_borrower_phone', 40)->nullable();
            $table->string('second_co_borrower_email')->nullable();
            $table->date('second_co_borrower_date_of_birth')->nullable();
            $table->string('second_co_borrower_id_type')->nullable();
            $table->string('second_co_borrower_id_number', 100)->nullable();
            $table->string('second_co_borrower_id_file_path')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->dropColumn([
                'co_borrower_address', 'co_borrower_phone', 'co_borrower_email', 'co_borrower_date_of_birth', 'co_borrower_id_type', 'co_borrower_id_number', 'co_borrower_id_file_path',
                'second_co_borrower_name', 'second_co_borrower_address', 'second_co_borrower_phone', 'second_co_borrower_email', 'second_co_borrower_date_of_birth', 'second_co_borrower_id_type', 'second_co_borrower_id_number', 'second_co_borrower_id_file_path',
            ]);
        });

        Schema::table('borrowers', function (Blueprint $table) {
            $table->dropColumn(['date_of_birth', 'id_type', 'id_number', 'id_file_path']);
        });
    }
};
