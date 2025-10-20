<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Role;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Spatie\Permission\Models\Permission;

class RoleStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $totalRoles = Role::count();
        $totalPermissions = Permission::count();
        $mostAssignedRole = Role::withCount('users')
            ->orderBy('users_count', 'desc')
            ->first();
        $recentlyCreated = Role::where('created_at', '>=', now()->subDays(7))
            ->count();

        return [
            Stat::make('Total Roles', $totalRoles)
                ->description('Roles in the system')
                ->descriptionIcon('heroicon-o-shield-check')
                ->color('success')
                ->chart([7, 3, 4, 5, 6, 3, 5, 3]),
            Stat::make('Total Permissions', $totalPermissions)
                ->description('Available permissions')
                ->descriptionIcon('heroicon-o-key')
                ->color('info')
                ->chart([5, 2, 10, 3, 15, 4, 17]),
            Stat::make('Most Assigned Role', $mostAssignedRole?->display_name ?? 'N/A')
                ->description($mostAssignedRole ? "{$mostAssignedRole->users_count} users" : 'No users yet')
                ->descriptionIcon('heroicon-o-users')
                ->color('warning'),
            Stat::make('Recent Roles', $recentlyCreated)
                ->description('Created this week')
                ->descriptionIcon('heroicon-o-calendar')
                ->color($recentlyCreated > 0 ? 'success' : 'gray'),
        ];
    }
}
