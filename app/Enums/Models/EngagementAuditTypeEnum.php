<?php

declare(strict_types=1);

namespace App\Enums\Models;

enum EngagementAuditTypeEnum: string
{
    case SINGLE = 'SINGLE';
    case COMPREHENSIVE = 'COMPREHENSIVE';
    case ENTERPRISE = 'ENTERPRISE';
    case SOFT1 = 'SOFT1';
    case SOFT2 = 'SOFT2';
    case SOFT3 = 'SOFT3';
}
