<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Role;
use Filament\Widgets\ChartWidget;

class RoleDistributionChart extends ChartWidget
{
    protected static ?string $heading = 'User Distribution by Role';

    protected static ?string $description = 'Number of users assigned to each role';

    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $roles = Role::withCount('users')
            ->orderBy('users_count', 'desc')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Users per Role',
                    'data' => $roles->pluck('users_count')->toArray(),
                    'backgroundColor' => [
                        'rgb(239, 68, 68)',      // red for super_admin
                        'rgb(245, 158, 11)',     // yellow for admin
                        'rgb(34, 197, 94)',      // green for user
                        'rgb(59, 130, 246)',     // blue for others
                        'rgb(168, 85, 247)',     // purple
                        'rgb(236, 72, 153)',     // pink
                    ],
                    'borderColor' => [
                        'rgb(220, 38, 38)',
                        'rgb(217, 119, 6)',
                        'rgb(22, 163, 74)',
                        'rgb(37, 99, 235)',
                        'rgb(147, 51, 234)',
                        'rgb(219, 39, 119)',
                    ],
                    'borderWidth' => 2,
                ],
            ],
            'labels' => $roles->pluck('display_name')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
                ],
                'tooltip' => [
                    'enabled' => true,
                ],
            ],
            'maintainAspectRatio' => true,
        ];
    }
}
