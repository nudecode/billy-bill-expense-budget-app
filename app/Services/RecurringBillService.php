<?php

namespace App\Services;

use App\DTOs\BillInstance;
use App\Models\Payment;
use App\Models\RecurringBill;
use Carbon\Carbon;

class RecurringBillService
{
    public function getForMonth(int $userId, int $year, int $month): array
    {
        return $this->generate($userId, $year, $month);
    }

    public function clearCache(int $userId, int $year, int $month): void
    {
        // Cache disabled — Eloquent models with relationships don't serialize cleanly
    }

    private function generate(int $userId, int $year, int $month): array
    {
        $monthStart = Carbon::create($year, $month, 1)->startOfDay();
        $monthEnd   = Carbon::create($year, $month, 1)->endOfMonth()->endOfDay();

        $rules = RecurringBill::with(['biller', 'category', 'subcategory', 'account', 'frequency'])
            ->where('user_id', $userId)
            ->where('start_date', '<=', $monthEnd)
            ->where(fn ($q) => $q->whereNull('end_date')->orWhere('end_date', '>=', $monthStart))
            ->get();

        // Index paid payments for this month by rule_id + date
        $payments = Payment::where('user_id', $userId)
            ->whereNotNull('recurring_bill_id')
            ->where(function ($q) use ($year, $month) {
                $q->whereYear('recurring_bill_date', $year)
                  ->whereMonth('recurring_bill_date', $month);
            })
            ->get()
            ->keyBy(fn ($p) => $p->recurring_bill_id . '_' . $p->recurring_bill_date->toDateString());

        $instances = [];

        foreach ($rules as $rule) {
            $date    = $rule->start_date->copy();
            $endDate = $rule->end_date ? $rule->end_date->copy() : $monthEnd;

            while ($date->lte($endDate)) {
                if ($date->year === $year && $date->month === $month) {
                    $key     = $rule->id . '_' . $date->toDateString();
                    $payment = $payments->get($key);

                    $instances[] = new BillInstance(
                        rule: $rule,
                        date: $date->copy(),
                        isPaid: $payment !== null,
                        payment: $payment,
                    );
                } elseif ($date->year > $year || ($date->year === $year && $date->month > $month)) {
                    // Past the target month — stop iterating this rule
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
            'week'  => $d->addWeeks($frequency->date_add_value),
            'month' => $d->addMonths($frequency->date_add_value),
            'year'  => $d->addYears($frequency->date_add_value),
            'day'   => $d->addDays($frequency->date_add_value),
            default => throw new \InvalidArgumentException("Unknown unit: {$frequency->date_add_unit}"),
        };
    }
}
