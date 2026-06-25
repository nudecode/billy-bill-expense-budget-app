<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function accounts(): HasMany        { return $this->hasMany(Account::class); }
    public function billers(): HasMany         { return $this->hasMany(Biller::class); }
    public function recurringBills(): HasMany  { return $this->hasMany(RecurringBill::class); }
    public function oneOffBills(): HasMany     { return $this->hasMany(OneOffBill::class); }
    public function payments(): HasMany        { return $this->hasMany(Payment::class); }
    public function recurringIncome(): HasMany { return $this->hasMany(RecurringIncome::class); }
    public function oneOffIncome(): HasMany    { return $this->hasMany(OneOffIncome::class); }
}
