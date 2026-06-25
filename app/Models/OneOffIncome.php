<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class OneOffIncome extends Model {
    use HasFactory;
    protected $table = 'one_off_income';
    protected $fillable = ['user_id','name','account_id','amount','income_date'];
    protected $casts = ['income_date'=>'date','amount'=>'decimal:2'];
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function account(): BelongsTo { return $this->belongsTo(Account::class); }
}
