<div class="card bg-base-100 shadow">
    <div class="card-body">
        <h2 class="card-title text-lg mb-4">
            <span class="icon-[tabler--chart-donut-2] size-5"></span>
            Team Health Gauge
        </h2>

        <div class="flex items-center justify-center">
            <div class="relative w-48 h-28">
                @php
                    $percentage = (float) $stats['percentage'];
                    // Arc length calculation for semi-circle
                    // Semi-circle circumference = π * r = π * 80 ≈ 251.33
                    $arcLength = 251.33;
                    $filledLength = ($percentage / 100) * $arcLength;
                    $emptyLength = $arcLength - $filledLength;
                @endphp
                <svg class="w-full h-full" viewBox="0 0 200 110">
                    <!-- Background arc (gray) -->
                    <path
                        d="M 20 100 A 80 80 0 0 1 180 100"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="16"
                        stroke-linecap="round"
                        class="text-base-300"
                    />
                    <!-- Progress arc (colored) -->
                    <path
                        d="M 20 100 A 80 80 0 0 1 180 100"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="16"
                        stroke-linecap="round"
                        stroke-dasharray="{{ $filledLength }} {{ $emptyLength }}"
                        class="{{ $percentage >= 75 ? 'text-success' : ($percentage >= 50 ? 'text-warning' : 'text-error') }}"
                    />
                </svg>

                <!-- Percentage Text -->
                <div class="absolute inset-0 flex items-end justify-center pb-1">
                    <span class="text-3xl font-bold {{ $percentage >= 75 ? 'text-success' : ($percentage >= 50 ? 'text-warning' : 'text-error') }}">
                        {{ number_format($percentage, 0) }}%
                    </span>
                </div>
            </div>
        </div>

        <div class="text-center mt-2">
            <p class="text-base-content/70">
                <span class="font-semibold text-success">{{ $stats['on_track'] }}</span> of
                <span class="font-semibold">{{ $stats['total'] }}</span> members on track
            </p>
        </div>

        @if($stats['off_track'] > 0)
        <div class="mt-3 pt-3 border-t border-base-200">
            <p class="text-sm text-error flex items-center gap-1">
                <span class="icon-[tabler--alert-circle] size-4"></span>
                {{ $stats['off_track'] }} {{ $stats['off_track'] === 1 ? 'member needs' : 'members need' }} attention
            </p>
        </div>
        @endif
    </div>
</div>
