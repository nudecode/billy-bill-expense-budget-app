<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};
class Biller extends Model {
    protected $fillable = ['user_id', 'name', 'phone', 'email', 'account_number'];
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function recurringBills(): HasMany { return $this->hasMany(RecurringBill::class); }
    public function oneOffBills(): HasMany { return $this->hasMany(OneOffBill::class); }
    public function payments(): HasMany { return $this->hasMany(Payment::class); }
}
