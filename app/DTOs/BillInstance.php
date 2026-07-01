<?php

namespace App\DTOs;

use App\Models\Payment;
use App\Models\RecurringBill;
use App\Models\RecurringBillOverride;
use Carbon\Carbon;

class BillInstance
{
    public function __construct(
        public readonly RecurringBill $rule,
        public readonly Carbon $date,
        public readonly bool $isPaid,
        public readonly ?Payment $payment = null,
        public readonly ?RecurringBillOverride $override = null,
    ) {}

    public function getAmount(): float
    {
        return (float) ($this->override?->amount ?? $this->rule->amount);
    }

    public function getBillerName(): string
    {
        return $this->rule->biller->name;
    }

    public function getCategoryName(): string
    {
        return $this->override?->category?->name ?? $this->rule->category->name;
    }

    public function getSubcategoryName(): ?string
    {
        return $this->rule->subcategory?->name;
    }

    public function getFrequencyName(): string
    {
        return $this->rule->frequency->name;
    }

    public function getAccountName(): string
    {
        return $this->override?->account?->name ?? $this->rule->account->name;
    }

    public function isOverridden(): bool
    {
        return $this->override !== null;
    }
}
