<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecurringBillOverride extends Model
{
    use HasFactory;

    protected $fillable = ['recurring_bill_id', 'occurrence_date', 'is_skipped', 'amount', 'category_id', 'account_id'];

    protected $casts = ['occurrence_date' => 'date', 'is_skipped' => 'boolean', 'amount' => 'decimal:2'];

    public function recurringBill(): BelongsTo
    {
        return $this->belongsTo(RecurringBill::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}
