<?php

namespace App\DTOs;

use App\Models\Payment;
use App\Models\RecurringBill;
use Carbon\Carbon;

class BillInstance
{
    public function __construct(
        public readonly RecurringBill $rule,
        public readonly Carbon $date,
        public readonly bool $isPaid,
        public readonly ?Payment $payment = null,
    ) {}

    public function getAmount(): float   { return (float) $this->rule->amount; }
    public function getBillerName(): string { return $this->rule->biller->name; }
    public function getCategoryName(): string { return $this->rule->category->name; }
    public function getSubcategoryName(): ?string { return $this->rule->subcategory?->name; }
    public function getFrequencyName(): string { return $this->rule->frequency->name; }
    public function getAccountName(): string { return $this->rule->account->name; }
}
