<?php

namespace App\DTOs;

use App\Models\RecurringIncome;
use Carbon\Carbon;

class IncomeInstance
{
    public function __construct(
        public readonly RecurringIncome $rule,
        public readonly Carbon $date,
    ) {}

    public function getAmount(): float  { return (float) $this->rule->amount; }
    public function getName(): string   { return $this->rule->name; }
    public function getAccountName(): string { return $this->rule->account->name; }
    public function getFrequencyName(): string { return $this->rule->frequency->name; }
}
