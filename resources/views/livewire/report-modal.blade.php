<div>
    @if ($show)
        <div
            x-data="{ show: @entangle('show') }"
            x-show="show"
            x-transition.opacity
            class="fixed inset-0 z-50 overflow-y-auto"
            style="display: none;"
        >
            <!-- Backdrop -->
            <div 
                class="fixed inset-0 bg-black bg-opacity-50"
                @click="$wire.close()"
            ></div>

            <!-- Modal -->
            <div class="flex min-h-screen items-center justify-center p-4">
                <div
                    @click.away="$wire.close()"
                    class="relative w-full max-w-lg rounded-2xl bg-white p-6 shadow-2xl"
                    style="max-width: 36rem;"
                >
                    <!-- Header -->
                    <div class="mb-4 flex items-start justify-between gap-4">
                        <div>
                            <h2 class="text-xl font-bold text-gray-900">{{ $title }}</h2>
                            <p class="mt-2 text-sm text-gray-600">
                                Tell us what is wrong and we will send it to the moderation inbox.
                            </p>
                        </div>
                        <button
                            type="button"
                            wire:click="close"
                            class="text-gray-400 hover:text-gray-600"
                        >
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- Form -->
                    <form wire:submit.prevent="submit" class="space-y-4">
                        <!-- Report Type -->
                        <div>
                            <label for="type" class="block text-sm font-semibold text-gray-900">
                                Reason
                            </label>
                            <select
                                id="type"
                                wire:model="type"
                                class="mt-2 block w-full rounded-xl border border-gray-300 px-4 py-3 focus:border-gray-900 focus:outline-none focus:ring-1 focus:ring-gray-900"
                                required
                            >
                                @foreach ($typeOptions as $option)
                                    <option value="{{ $option['value'] }}">
                                        {{ $option['label'] }}
                                    </option>
                                @endforeach
                            </select>
                            @error('type')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Description -->
                        @if ($type && !empty($typeOptions))
                            @php
                                $selectedOption = collect($typeOptions)->firstWhere('value', $type);
                                $description = $selectedOption['description'] ?? '';
                            @endphp
                            @if ($description)
                                <p class="text-sm text-gray-600">{{ $description }}</p>
                            @endif
                        @endif

                        <!-- Message -->
                        <div>
                            <label for="message" class="block text-sm font-semibold text-gray-900">
                                Extra details
                            </label>
                            <textarea
                                id="message"
                                wire:model="message"
                                rows="4"
                                placeholder="Add context for your report"
                                class="mt-2 block w-full rounded-xl border border-gray-300 px-4 py-3 focus:border-gray-900 focus:outline-none focus:ring-1 focus:ring-gray-900"
                            ></textarea>
                            @error('message')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Actions -->
                        <div class="flex justify-end gap-3 pt-2">
                            <button
                                type="button"
                                wire:click="close"
                                class="rounded-full border border-gray-300 px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50"
                            >
                                {{ $cancelLabel }}
                            </button>
                            <button
                                type="submit"
                                class="rounded-full bg-gray-900 px-5 py-2.5 text-sm font-medium text-white hover:bg-gray-800"
                            >
                                {{ $submitLabel }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
