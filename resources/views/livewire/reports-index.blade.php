<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900">Reports</h1>
    </div>

    <!-- Filters -->
    <div class="flex flex-col gap-4 sm:flex-row">
        <!-- Search -->
        <div class="flex-1">
            <input
                type="text"
                wire:model.live.debounce.300ms="search"
                placeholder="Search reports..."
                class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-gray-900 focus:outline-none focus:ring-1 focus:ring-gray-900"
            />
        </div>

        <!-- Status Filter -->
        <div>
            <select
                wire:model.live="status"
                class="rounded-lg border border-gray-300 px-4 py-2 focus:border-gray-900 focus:outline-none focus:ring-1 focus:ring-gray-900"
            >
                <option value="all">All Status</option>
                <option value="pending">Pending</option>
                <option value="reviewing">Reviewing</option>
                <option value="resolved">Resolved</option>
                <option value="dismissed">Dismissed</option>
            </select>
        </div>
    </div>

    <!-- Reports List -->
    <div class="space-y-4">
        @forelse ($reports as $report)
            <div class="rounded-lg border border-gray-200 bg-white p-6">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <!-- Report Type -->
                        <div class="flex items-center gap-3">
                            <span class="inline-flex rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-800">
                                {{ $report->type }}
                            </span>
                            <span class="inline-flex rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-800">
                                {{ $report->status }}
                            </span>
                        </div>

                        <!-- Message -->
                        @if ($report->data['message'] ?? null)
                            <p class="mt-3 text-gray-700">{{ $report->data['message'] }}</p>
                        @endif

                        <!-- Meta -->
                        <div class="mt-4 flex items-center gap-4 text-sm text-gray-500">
                            <span>
                                Reported by: <strong>{{ $report->reporter?->name ?? 'Anonymous' }}</strong>
                            </span>
                            <span>•</span>
                            <span>{{ $report->created_at->diffForHumans() }}</span>
                        </div>

                        <!-- Reportable Info -->
                        <div class="mt-2 text-sm text-gray-500">
                            <span>
                                Reportable: {{ class_basename($report->reportable_type) }} #{{ $report->reportable_id }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="rounded-lg border border-gray-200 bg-white p-12 text-center">
                <p class="text-gray-500">No reports found.</p>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $reports->links() }}
    </div>
</div>
