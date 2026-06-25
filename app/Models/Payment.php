<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class Payment extends Model {
    use HasFactory;
    protected $fillable = ['user_id','recurring_bill_id','recurring_bill_date','one_off_bill_id','biller_id','account_id','category_id','amount','payment_date','reference_number','notes'];
    protected $casts = ['recurring_bill_date'=>'date','payment_date'=>'date','amount'=>'decimal:2'];
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function recurringBill(): BelongsTo { return $this->belongsTo(RecurringBill::class); }
    public function oneOffBill(): BelongsTo { return $this->belongsTo(OneOffBill::class); }
    public function biller(): BelongsTo { return $this->belongsTo(Biller::class); }
    public function account(): BelongsTo { return $this->belongsTo(Account::class); }
    public function category(): BelongsTo { return $this->belongsTo(Category::class); }
}
