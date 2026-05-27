<?php

namespace App\Modules\Lgpd\Application\DTOs;

use Carbon\Carbon;

class ComplianceReportFilterDTO
{
    public function __construct(
        public readonly Carbon $startDate,
        public readonly Carbon $endDate,
    ) {}
}
