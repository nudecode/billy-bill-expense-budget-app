<?php

namespace App\Services;

use App\DTOs\IncomeInstance;
use App\Models\RecurringIncome;
use App\Models\RecurringIncomeOverride;
use Carbon\Carbon;

class RecurringIncomeService
{
    public function getForMonth(int $userId, int $year, int $month): array
    {
        return $this->generate($userId, $year, $month);
    }

    public function clearCache(int $userId, int $year, int $month): void
    {
        // Cache disabled — add back with array serialisation later
    }

    private function generate(int $userId, int $year, int $month): array
    {
        $monthStart = Carbon::create($year, $month, 1)->startOfDay();
        $monthEnd = Carbon::create($year, $month, 1)->endOfMonth()->endOfDay();

        $rules = RecurringIncome::with(['frequency', 'account'])
            ->where('user_id', $userId)
            ->where('start_date', '<=', $monthEnd)
            ->where(fn ($q) => $q->whereNull('end_date')->orWhere('end_date', '>=', $monthStart))
            ->get();

        // Index occurrence overrides (single-occurrence edits/skips) for this month by rule_id + date
        $overrides = RecurringIncomeOverride::with('account')
            ->whereIn('recurring_income_id', $rules->pluck('id'))
            ->whereYear('occurrence_date', $year)
            ->whereMonth('occurrence_date', $month)
            ->get()
            ->keyBy(fn ($o) => $o->recurring_income_id.'_'.$o->occurrence_date->toDateString());

        $instances = [];

        foreach ($rules as $rule) {
            $date = $rule->start_date->copy();
            $endDate = $rule->end_date ? $rule->end_date->copy() : $monthEnd;

            while ($date->lte($endDate)) {
                if ($date->year === $year && $date->month === $month) {
                    $key = $rule->id.'_'.$date->toDateString();
                    $override = $overrides->get($key);

                    if (! $override?->is_skipped) {
                        $instances[] = new IncomeInstance(rule: $rule, date: $date->copy(), override: $override);
                    }
                } elseif ($date->year > $year || ($date->year === $year && $date->month > $month)) {
                    break;
                }

                $date = $this->advance($date, $rule->frequency);
            }
        }

        usort($instances, fn ($a, $b) => $a->date->timestamp <=> $b->date->timestamp);

        return $instances;
    }

    private function advance(Carbon $date, $frequency): Carbon
    {
        $d = $date->copy();

        return match ($frequency->date_add_unit) {
            'week' => $d->addWeeks($frequency->date_add_value),
            'month' => $d->addMonths($frequency->date_add_value),
            'year' => $d->addYears($frequency->date_add_value),
            'day' => $d->addDays($frequency->date_add_value),
            default => throw new \InvalidArgumentException("Unknown unit: {$frequency->date_add_unit}"),
        };
    }
}
