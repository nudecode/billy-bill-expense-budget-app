<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};
class Account extends Model {
    protected $fillable = ['user_id', 'name'];
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function recurringBills(): HasMany { return $this->hasMany(RecurringBill::class); }
    public function oneOffBills(): HasMany { return $this->hasMany(OneOffBill::class); }
    public function recurringIncome(): HasMany { return $this->hasMany(RecurringIncome::class); }
    public function oneOffIncome(): HasMany { return $this->hasMany(OneOffIncome::class); }
}
