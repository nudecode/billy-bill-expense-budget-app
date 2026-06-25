<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('recurring_bill_id')->nullable()->constrained()->nullOnDelete();
            $table->date('recurring_bill_date')->nullable();
            $table->foreignId('one_off_bill_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('biller_id')->constrained()->cascadeOnDelete();
            $table->foreignId('account_id')->constrained();
            $table->foreignId('category_id')->constrained();
            $table->decimal('amount', 10, 2);
            $table->date('payment_date');
            $table->timestamps();

            $table->index(['user_id', 'recurring_bill_id', 'recurring_bill_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
