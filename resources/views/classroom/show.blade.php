@extends('layouts.app')

@section('content')
    <div 
        x-data="classroomDisplay({
            schoolId: {{ $homeroom->school_id }},
            students: @js($students),
        })"
        class="space-y-6"
    >
        <div>
            <h1 class="text-2xl font-semibold text-slate-800">{{ $homeroom->name }} Classroom</h1>
            <p class="text-sm text-slate-500">Teacher: {{ $homeroom->teacher_name }}</p>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <template x-for="student in students" :key="student.id">
                <div 
                    class="rounded-lg border p-4 shadow-sm transition-colors"
                    :class="{
                        'border-blue-300 bg-blue-50': student.status === 'released',
                        'border-slate-200 bg-white': student.status !== 'released'
                    }"
                >
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <div class="text-lg font-semibold text-slate-800" x-text="student.name"></div>
                            <div class="text-sm text-slate-500">
                                Lane: <span x-text="student.lane || '—'"></span> • Position: <span x-text="student.position || '—'"></span>
                            </div>
                            <div x-show="student.status === 'released'" class="mt-2 text-xs text-green-600 font-medium">
                                ✓ Sent
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <span 
                                class="rounded-full px-3 py-1 text-xs font-semibold uppercase"
                                :class="{
                                    'bg-green-100 text-green-700': student.indicator === 'Send Now',
                                    'bg-yellow-100 text-yellow-700': student.indicator === 'Get Ready',
                                    'bg-blue-100 text-blue-700': student.indicator === 'Waiting',
                                    'bg-gray-100 text-gray-700': student.status === 'released'
                                }"
                                x-text="student.indicator"
                            ></span>
                            <button
                                x-show="student.call_id && student.status !== 'released'"
                                @click="markAsSent(student.call_id, student.name)"
                                class="rounded bg-green-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-green-700 transition disabled:opacity-50 disabled:cursor-not-allowed"
                                :disabled="!student.call_id || student.status === 'released'"
                                x-text="'✓ Mark as Sent'"
                            ></button>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        @if ($canViewQueue)
            <div class="rounded border border-dashed border-blue-300 bg-blue-50 p-4 text-sm text-blue-700">
                Full queue access enabled for this classroom. Monitor the live queue from the admin menu.
            </div>
        @endif
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('classroomDisplay', (config) => ({
                schoolId: config.schoolId,
                students: config.students,

                init() {
                    // Debug: Log student data to verify call_id is present
                    console.log('Students data:', this.students);
                    this.students.forEach((student, index) => {
                        console.log(`Student ${index + 1}:`, {
                            name: student.name,
                            call_id: student.call_id,
                            status: student.status,
                            in_queue: student.in_queue,
                            canShowButton: student.call_id && student.status !== 'released'
                        });
                    });
                    this.setupWebSocket();
                },

                setupWebSocket() {
                    if (!window.Echo) {
                        console.error('Echo not available');
                        return;
                    }

                    const channel = window.Echo.channel(`school.${this.schoolId}`);

                    channel.listen('CallUpdated', (data) => {
                        console.log('CallUpdated event received:', data);
                        this.refreshStudents();
                    }).error((error) => {
                        console.error('Error listening to CallUpdated:', error);
                    });

                    channel.listen('QueueUpdated', (data) => {
                        console.log('QueueUpdated event received:', data);
                        this.refreshStudents();
                    }).error((error) => {
                        console.error('Error listening to QueueUpdated:', error);
                    });
                },

                async refreshStudents() {
                    try {
                        // Reload the page to get updated student data
                        window.location.reload();
                    } catch (error) {
                        console.error('Failed to refresh students', error);
                    }
                },

                async markAsSent(callId, studentName) {
                    if (!callId) {
                        console.error('No call_id provided');
                        alert('Student is not in queue.');
                        return;
                    }

                    if (!confirm(`Mark ${studentName} as sent?`)) {
                        return;
                    }

                    try {
                        const response = await window.axios.patch(`/queue/calls/${callId}`, {
                            status: 'released'
                        });
                        
                        console.log('Student marked as sent successfully:', response);
                        
                        // Update local state immediately
                        const student = this.students.find(s => s.call_id === callId);
                        if (student) {
                            student.status = 'released';
                            student.in_queue = false;
                            student.indicator = 'Sent';
                        }
                        
                        // Queue will refresh automatically via WebSocket
                    } catch (error) {
                        console.error('Error marking student as sent:', error);
                        if (error.response) {
                            console.error('Response status:', error.response.status);
                            console.error('Response data:', error.response.data);
                            alert(`Failed to mark student as sent (${error.response.status}). Please check the console for details.`);
                        } else {
                            alert('Failed to mark student as sent. Please try again.');
                        }
                    }
                },
            }));
        });
    </script>
@endsection

