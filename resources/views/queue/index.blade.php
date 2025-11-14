@extends('layouts.app')

@section('content')
    <div
        x-data="queueBoard({
            lanes: @js($lanes),
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
                                <div class="mb-3 flex items-center justify-between border-b border-slate-200 pb-2">
                                    <button
                                        @click="markPickedUp(checkin.id)"
                                        class="flex items-center gap-1 rounded-md bg-blue-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-blue-700 transition"
                                        title="Mark all students as picked up and remove car from queue"
                                    >
                                        ✓ Mark Picked Up
                                    </button>
                                    <button
                                        @click="removeCheckin(checkin.id)"
                                        class="rounded px-2 py-1 text-xs font-medium text-red-600 hover:bg-red-50 transition"
                                        title="Remove entire checkin from queue"
                                    >
                                        ✕ Remove
                                    </button>
                                </div>
                                <div class="flex items-center justify-between mb-2">
                                    <div>
                                        <div class="text-sm font-semibold text-slate-900" x-text="checkin.driver"></div>
                                        <div class="text-xs text-slate-500">Position <span x-text="checkin.position"></span></div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <div class="text-xs uppercase tracking-wide text-slate-600" x-text="checkin.status"></div>
                                    </div>
                                </div>
                                <div class="mt-3 space-y-1">
                                    <template x-for="(student, index) in checkin.students" :key="student.id || index">
                                        <div class="flex items-center justify-between rounded bg-white/50 px-3 py-2 text-sm"
                                            :class="student.status === 'released' ? 'opacity-60 bg-green-50' : 'text-slate-700'">
                                            <span x-text="student.name"></span>
                                            <button
                                                @click="markReleased(student.id, student.name)"
                                                class="rounded px-2 py-1 text-xs font-medium transition"
                                                :class="student.status === 'released' 
                                                    ? 'text-green-600 cursor-default' 
                                                    : 'text-blue-600 hover:bg-blue-50'"
                                                :disabled="student.status === 'released'"
                                                title="Mark student as picked up"
                                            >
                                                <template x-if="student.status === 'released'">
                                                    <span>✓ Picked Up</span>
                                                </template>
                                                <template x-if="student.status !== 'released'">
                                                    <span>✓ Mark Picked Up</span>
                                                </template>
                                            </button>
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
                    console.log('Initializing queue board for school:', this.schoolId);
                    
                    if (!window.Echo) {
                        console.warn('⚠️ Echo is not available');
                        return;
                    }

                    console.log('Echo is available, subscribing to channel:', `school.${this.schoolId}`);
                    
                    const channel = window.Echo.channel(`school.${this.schoolId}`);
                    
                    // Log subscription
                    channel.subscribed(() => {
                        console.log(`✅ Subscribed to channel: school.${this.schoolId}`);
                    });

                    channel.error((error) => {
                        console.error('❌ Channel subscription error:', error);
                    });

                    // Listen for events - use broadcastAs() names directly
                    channel.listen('CheckinCreated', (data) => {
                        console.log('📢 CheckinCreated event received via listener:', data);
                        this.refreshQueue();
                    }).error((error) => {
                        console.error('Error listening to CheckinCreated:', error);
                    });

                    channel.listen('QueueUpdated', (data) => {
                        console.log('📢 QueueUpdated event received via listener:', data);
                        this.refreshQueue();
                    }).error((error) => {
                        console.error('Error listening to QueueUpdated:', error);
                    });

                    channel.listen('CallUpdated', (data) => {
                        console.log('📢 CallUpdated event received via listener:', data);
                        this.refreshQueue();
                    }).error((error) => {
                        console.error('Error listening to CallUpdated:', error);
                    });

                    // Log errors
                    window.Echo.connector.pusher.connection.bind('error', (error) => {
                        console.error('❌ Echo connection error:', error);
                    });

                    // Log all messages for debugging and handle events
                    window.Echo.connector.pusher.connection.bind('message', (message) => {
                        console.log('📨 Echo message received:', message);
                        
                        // Handle events that might not be caught by listeners
                        if (message.event === 'CheckinCreated' || message.event === 'QueueUpdated' || message.event === 'CallUpdated') {
                            if (message.channel === `school.${this.schoolId}`) {
                                console.log(`📢 ${message.event} caught via message handler, refreshing queue...`);
                                this.refreshQueue();
                            }
                        }
                    });
                },

                async refreshQueue() {
                    try {
                        const response = await window.axios.get('{{ route('queue.index') }}', {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json',
                            },
                            params: {
                                school_id: this.schoolId,
                            },
                        });

                        if (response?.data?.lanes) {
                            this.lanes = response.data.lanes.map((lane) => ({
                                number: lane.number,
                                checkins: lane.checkins ?? [],
                            }));
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

                async markPickedUp(checkinId) {
                    if (!confirm('Mark all students in this car as picked up and remove from queue?')) {
                        return;
                    }

                    try {
                        await window.axios.post(`/queue/checkins/${checkinId}/mark-picked-up`);
                        // Queue will refresh automatically via WebSocket
                    } catch (error) {
                        console.error('Error marking checkin as picked up:', error);
                        alert('Failed to mark car as picked up. Please try again.');
                    }
                },

                async markReleased(callId, studentName) {
                    if (!confirm(`Mark ${studentName} as picked up?`)) {
                        return;
                    }

                    try {
                        await window.axios.patch(`/queue/calls/${callId}`, {
                            status: 'released'
                        });
                        // Queue will refresh automatically via WebSocket
                    } catch (error) {
                        console.error('Error marking student as released:', error);
                        alert('Failed to mark student as picked up. Please try again.');
                    }
                },

                async removeCheckin(checkinId) {
                    if (!confirm('Remove this entire checkin (driver and all students) from the queue?')) {
                        return;
                    }

                    try {
                        await window.axios.delete(`/queue/checkins/${checkinId}`);
                        // Queue will refresh automatically via WebSocket
                    } catch (error) {
                        console.error('Error removing checkin:', error);
                        alert('Failed to remove checkin. Please try again.');
                    }
                },
            }));
        });
    </script>
@endsection

