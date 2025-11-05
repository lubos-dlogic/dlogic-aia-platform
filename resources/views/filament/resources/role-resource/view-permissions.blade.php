<div class="space-y-4">
    @if($role->permissions->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach($role->permissions->groupBy(fn($permission) => explode('_', $permission->name)[count(explode('_', $permission->name)) - 1]) as $resource => $permissions)
                <div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                    <h3 class="font-semibold text-sm text-gray-900 dark:text-gray-100 mb-2 capitalize">
                        {{ ucwords(str_replace('_', ' ', $resource)) }} Permissions
                    </h3>
                    <ul class="space-y-1">
                        @foreach($permissions as $permission)
                            <li class="flex items-center text-sm text-gray-700 dark:text-gray-300">
                                <svg class="w-4 h-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                {{ ucwords(str_replace(['_', 'any'], [' ', 'Any'], $permission->name)) }}
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>
        <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
            <p class="text-sm text-gray-600 dark:text-gray-400">
                <strong>Total Permissions:</strong> {{ $role->permissions->count() }}
            </p>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                <strong>Users with this role:</strong> {{ $role->users_count }}
            </p>
        </div>
    @else
        <div class="text-center py-8 text-gray-500 dark:text-gray-400">
            <svg class="w-12 h-12 mx-auto mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
            </svg>
            <p class="text-sm font-medium">No permissions assigned to this role</p>
            <p class="text-xs mt-1">Edit the role to assign permissions</p>
        </div>
    @endif
</div>