<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecurringIncomeOverride extends Model
{
    use HasFactory;

    protected $fillable = ['recurring_income_id', 'occurrence_date', 'is_skipped', 'amount', 'account_id'];

    protected $casts = ['occurrence_date' => 'date', 'is_skipped' => 'boolean', 'amount' => 'decimal:2'];

    public function recurringIncome(): BelongsTo
    {
        return $this->belongsTo(RecurringIncome::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}
