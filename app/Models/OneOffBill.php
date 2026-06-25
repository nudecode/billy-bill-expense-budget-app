<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class OneOffBill extends Model {
    protected $fillable = ['user_id','biller_id','category_id','subcategory_id','account_id','amount','due_date','is_paid','date_paid'];
    protected $casts = ['due_date'=>'date','date_paid'=>'date','is_paid'=>'boolean','amount'=>'decimal:2'];
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function biller(): BelongsTo { return $this->belongsTo(Biller::class); }
    public function category(): BelongsTo { return $this->belongsTo(Category::class); }
    public function subcategory(): BelongsTo { return $this->belongsTo(Subcategory::class); }
    public function account(): BelongsTo { return $this->belongsTo(Account::class); }
}
