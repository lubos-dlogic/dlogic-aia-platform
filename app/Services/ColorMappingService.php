<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\Models\EngagementAuditTypeEnum;

class ColorMappingService
{
    /**
     * Get the Tailwind color for a given EngagementAuditTypeEnum value.
     *
     * @return string Tailwind color name (e.g., 'blue', 'purple')
     */
    public static function getAuditTypeColor(EngagementAuditTypeEnum $type): string
    {
        return match ($type) {
            EngagementAuditTypeEnum::SINGLE => 'blue',
            EngagementAuditTypeEnum::COMPREHENSIVE => 'purple',
            EngagementAuditTypeEnum::ENTERPRISE => 'indigo',
            EngagementAuditTypeEnum::SOFT1 => 'cyan',
            EngagementAuditTypeEnum::SOFT2 => 'teal',
            EngagementAuditTypeEnum::SOFT3 => 'emerald',
        };
    }

    /**
     * Get all audit type options as an array for form selects.
     *
     * @return array<string, string> Array with enum value as key and label as value
     */
    public static function getAuditTypeOptions(): array
    {
        return [
            EngagementAuditTypeEnum::SINGLE->value => 'Single',
            EngagementAuditTypeEnum::COMPREHENSIVE->value => 'Comprehensive',
            EngagementAuditTypeEnum::ENTERPRISE->value => 'Enterprise',
            EngagementAuditTypeEnum::SOFT1->value => 'SOFT1',
            EngagementAuditTypeEnum::SOFT2->value => 'SOFT2',
            EngagementAuditTypeEnum::SOFT3->value => 'SOFT3',
        ];
    }

    /**
     * Get the user-friendly label for a given audit type.
     */
    public static function getAuditTypeLabel(EngagementAuditTypeEnum $type): string
    {
        return match ($type) {
            EngagementAuditTypeEnum::SINGLE => 'Single',
            EngagementAuditTypeEnum::COMPREHENSIVE => 'Comprehensive',
            EngagementAuditTypeEnum::ENTERPRISE => 'Enterprise',
            EngagementAuditTypeEnum::SOFT1 => 'SOFT1',
            EngagementAuditTypeEnum::SOFT2 => 'SOFT2',
            EngagementAuditTypeEnum::SOFT3 => 'SOFT3',
        };
    }
}
