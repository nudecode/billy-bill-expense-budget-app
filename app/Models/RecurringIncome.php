<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RecurringIncome extends Model
{
    use HasFactory;

    protected $table = 'recurring_income';

    protected $fillable = ['user_id', 'name', 'frequency_id', 'account_id', 'amount', 'start_date', 'end_date'];

    protected $casts = ['start_date' => 'date', 'end_date' => 'date', 'amount' => 'decimal:2'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function frequency(): BelongsTo
    {
        return $this->belongsTo(Frequency::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function overrides(): HasMany
    {
        return $this->hasMany(RecurringIncomeOverride::class);
    }
}
