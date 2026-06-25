<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};
class RecurringBill extends Model {
    use HasFactory;
    protected $fillable = ['user_id','biller_id','frequency_id','category_id','subcategory_id','account_id','amount','start_date','end_date'];
    protected $casts = ['start_date'=>'date','end_date'=>'date','amount'=>'decimal:2'];
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function biller(): BelongsTo { return $this->belongsTo(Biller::class); }
    public function frequency(): BelongsTo { return $this->belongsTo(Frequency::class); }
    public function category(): BelongsTo { return $this->belongsTo(Category::class); }
    public function subcategory(): BelongsTo { return $this->belongsTo(Subcategory::class); }
    public function account(): BelongsTo { return $this->belongsTo(Account::class); }
    public function payments(): HasMany { return $this->hasMany(Payment::class); }
}
