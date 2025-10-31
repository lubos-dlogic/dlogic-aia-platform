<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            Activity Timeline
        </x-slot>

        <x-slot name="description">
            Recent activities for this engagement audit
        </x-slot>

        <div class="space-y-4">
            @forelse ($this->getActivities() as $activity)
                <div class="flex gap-4 border-l-2 border-gray-200 dark:border-gray-700 pl-4 pb-4">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-primary-500 rounded-full flex items-center justify-center">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center justify-between">
                            <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                {{ ucfirst($activity['event']) }}
                            </h4>
                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                {{ $activity['created_at']->diffForHumans() }}
                            </span>
                        </div>
                        <p class="text-sm text-gray-600 dark:text-gray-300 mt-1">
                            {{ $activity['description'] }}
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                            By: {{ $activity['causer'] }}
                        </p>
                        @if ($activity['properties']->isNotEmpty())
                            <details class="mt-2">
                                <summary class="text-xs text-gray-500 dark:text-gray-400 cursor-pointer">
                                    View Changes
                                </summary>
                                <pre class="text-xs bg-gray-100 dark:bg-gray-800 p-2 rounded mt-1 overflow-x-auto">{{ json_encode($activity['properties'], JSON_PRETTY_PRINT) }}</pre>
                            </details>
                        @endif
                    </div>
                </div>
            @empty
                <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <p class="mt-2">No activities recorded yet</p>
                </div>
            @endforelse
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
