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
        Schema::create('recurring_bill_overrides', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recurring_bill_id')->constrained()->cascadeOnDelete();
            $table->date('occurrence_date');
            $table->boolean('is_skipped')->default(false);
            $table->decimal('amount', 10, 2)->nullable();
            $table->foreignId('category_id')->nullable()->constrained();
            $table->foreignId('account_id')->nullable()->constrained();
            $table->timestamps();

            $table->unique(['recurring_bill_id', 'occurrence_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recurring_bill_overrides');
    }
};
