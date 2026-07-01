<?php

namespace App\DTOs;

use App\Models\RecurringIncome;
use App\Models\RecurringIncomeOverride;
use Carbon\Carbon;

class IncomeInstance
{
    public function __construct(
        public readonly RecurringIncome $rule,
        public readonly Carbon $date,
        public readonly ?RecurringIncomeOverride $override = null,
    ) {}

    public function getAmount(): float
    {
        return (float) ($this->override?->amount ?? $this->rule->amount);
    }

    public function getName(): string
    {
        return $this->rule->name;
    }

    public function getAccountName(): string
    {
        return $this->override?->account?->name ?? $this->rule->account->name;
    }

    public function getFrequencyName(): string
    {
        return $this->rule->frequency->name;
    }

    public function isOverridden(): bool
    {
        return $this->override !== null;
    }
}
