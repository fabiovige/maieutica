<?php

namespace App\Modules\Lgpd\Application\Services;

use Carbon\Carbon;

class BusinessDayCalculator
{
    /**
     * Lista de feriados fixos (MM-DD) e móveis (YYYY-MM-DD).
     */
    private array $fixedHolidays;

    private array $mobileHolidays;

    public function __construct()
    {
        $this->fixedHolidays = config('lgpd.holidays.fixed', []);
        $this->mobileHolidays = config('lgpd.holidays.mobile', []);
    }

    /**
     * Soma N dias úteis a partir de uma data, excluindo sábados, domingos e feriados nacionais.
     * O dia de início NÃO é contado.
     */
    public function addBusinessDays(Carbon $startDate, int $days): Carbon
    {
        $current = $startDate->copy();
        $added = 0;

        while ($added < $days) {
            $current->addDay();

            if ($this->isBusinessDay($current)) {
                $added++;
            }
        }

        return $current;
    }

    /**
     * Calcula dias úteis restantes entre hoje e o deadline.
     * Exclusivo de hoje, inclusivo do deadline (se for dia útil).
     */
    public function businessDaysRemaining(Carbon $deadline): int
    {
        $today = Carbon::today();
        $target = $deadline->copy()->startOfDay();

        if ($target->lte($today)) {
            return 0;
        }

        $count = 0;
        $current = $today->copy();

        while ($current->lt($target)) {
            $current->addDay();

            if ($this->isBusinessDay($current)) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Verifica se uma data é dia útil (não é fim de semana nem feriado nacional).
     */
    public function isBusinessDay(Carbon $date): bool
    {
        // Sábado ou domingo
        if ($date->isWeekend()) {
            return false;
        }

        // Feriado fixo (recorrente todo ano) — formato MM-DD
        $monthDay = $date->format('m-d');
        if (in_array($monthDay, $this->fixedHolidays, true)) {
            return false;
        }

        // Feriado móvel (data específica) — formato YYYY-MM-DD
        $fullDate = $date->format('Y-m-d');
        if (in_array($fullDate, $this->mobileHolidays, true)) {
            return false;
        }

        return true;
    }
}
