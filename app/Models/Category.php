<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Category extends Model {
    protected $fillable = ['name'];
    public function subcategories(): HasMany { return $this->hasMany(Subcategory::class); }
    public function recurringBills(): HasMany { return $this->hasMany(RecurringBill::class); }
    public function oneOffBills(): HasMany { return $this->hasMany(OneOffBill::class); }
}
