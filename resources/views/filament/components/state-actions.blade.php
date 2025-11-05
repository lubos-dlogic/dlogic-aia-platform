<div class="space-y-4">
    <div class="flex flex-col justify-items-center text-center">
        <p class="font-medium text-md mb-4" style="color:orangered !important;">
            Confirmation window IS NOT displayed upon clicking action button! Action is immediate!
        </p>

        <p class="font-medium">
            <span style="color:#999;">Current status:</span> {{ ucfirst($record->state->name()) }}
        </p>

        <p class="mt-2 mb-4">Changes <strong>ARE IMMEDIATE !</strong></p>
    </div>

    <div class="flex flex-wrap gap-2 justify-center">
        @php
            $allowed = $record->state->transitionableStates();
        @endphp

        @foreach ($allowed as $target)
            @php
                /**
                * @var \App\States\ClientState $instance
                */
                $instance = new $target($record);
                $color = $instance->color();
                $label = $instance->actionText();
            @endphp

            <x-filament::button
                color="{{ $color }}"
                size="sm"
                wire:click="transitionState({{ $record->id }}, '{{ addslashes($target) }}'); close();"
                :disabled="! auth()->user()->can('changeState', $record)"
            >
                {{ $label }}
            </x-filament::button>
        @endforeach

        @if (empty($allowed))
            <span class="text-gray-400 text-sm italic">No transitions available</span>
        @endif
    </div>

    <div class="pt-2 flex justify-end">
        <x-filament::button color="warning" size="sm" x-on:click="close()">
            Cancel
        </x-filament::button>
    </div>
</div>
