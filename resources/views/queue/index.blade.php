@extends('layouts.app')

@section('content')
    <div
        x-data="queueBoard({
            lanes: @json($lanes),
            schoolId: {{ $school->id }},
        })"
        class="space-y-6"
    >
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-slate-800">Live Queue</h1>
                <p class="text-sm text-slate-500">School: {{ $school->name }} • Lane mode: {{ $school->lane_color_mode }}</p>
            </div>
            <div class="flex items-center gap-3 text-sm text-slate-500">
                <span class="flex items-center gap-2"><span class="h-3 w-3 rounded bg-green-400"></span>Positions 1-5</span>
                <span class="flex items-center gap-2"><span class="h-3 w-3 rounded bg-yellow-300"></span>Positions 6-10</span>
                <span class="flex items-center gap-2"><span class="h-3 w-3 rounded bg-slate-300"></span>Positions 11+</span>
                <span class="flex items-center gap-2"><span class="h-3 w-3 rounded bg-blue-400"></span>Released</span>
                <span class="flex items-center gap-2"><span class="h-3 w-3 rounded bg-red-400"></span>Hold / Exception</span>
            </div>
        </div>

        <div class="grid gap-4" style="grid-template-columns: repeat({{ $school->lane_count }}, minmax(0, 1fr));">
            <template x-for="lane in lanes" :key="lane.number">
                <div class="rounded-xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-100 px-4 py-3 text-lg font-semibold text-blue-600">
                        Lane <span x-text="lane.number"></span>
                    </div>
                    <div class="space-y-3 p-4">
                        <template x-if="lane.checkins.length === 0">
                            <div class="rounded border border-dashed border-slate-200 bg-slate-50 p-4 text-center text-sm text-slate-500">
                                Waiting for arrivals
                            </div>
                        </template>

                        <template x-for="checkin in lane.checkins" :key="checkin.id">
                            <div
                                class="rounded-lg border p-4 transition"
                                :class="cardColor(checkin.color)"
                            >
                                <div class="flex items-center justify-between">
                                    <div>
                                        <div class="text-sm font-semibold text-slate-900" x-text="checkin.driver"></div>
                                        <div class="text-xs text-slate-500">Position <span x-text="checkin.position"></span></div>
                                    </div>
                                    <div class="text-xs uppercase tracking-wide text-slate-600" x-text="checkin.status"></div>
                                </div>
                                <div class="mt-3 space-y-1">
                                    <template x-for="student in checkin.students" :key="student">
                                        <div class="rounded bg-white/50 px-3 py-2 text-sm text-slate-700">
                                            <span x-text="student"></span>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('queueBoard', ({ lanes, schoolId }) => ({
                lanes,
                schoolId,

                init() {
                    if (window.Echo) {
                        window.Echo.channel(`school.${this.schoolId}`)
                            .listen('QueueUpdated', () => this.refreshQueue())
                            .listen('CheckinCreated', () => this.refreshQueue())
                            .listen('CallUpdated', () => this.refreshQueue());
                    }
                },

                async refreshQueue() {
                    try {
                        const response = await window.axios.get('{{ route('queue.index') }}', {
                            headers: { 'X-Requested-With': 'XMLHttpRequest' },
                        });

                        if (response?.data?.lanes) {
                            this.lanes = response.data.lanes;
                        }
                    } catch (error) {
                        console.error('Failed to refresh queue', error);
                    }
                },

                cardColor(color) {
                    return {
                        'bg-green-100 border-green-200': color === 'green',
                        'bg-yellow-100 border-yellow-200': color === 'yellow',
                        'bg-slate-100 border-slate-200': color === 'gray',
                        'bg-blue-100 border-blue-200': color === 'blue',
                        'bg-red-100 border-red-200': color === 'red',
                    };
                },
            }));
        });
    </script>
@endsection

