@extends('layouts.app')

@section('content')
    <div
        x-data="mobileEntry({
            lanes: @js($lanes),
            schoolId: {{ $school->id }},
            initialLaneEntries: @js($laneEntries),
        })"
        class="space-y-6"
    >
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-slate-800">Mobile Entry</h1>
                <p class="text-sm text-slate-500">School: {{ $school->name }} • Search students or drivers, drag into lane columns to queue manually.</p>
            </div>
            <div class="flex gap-2">
                <input
                    type="text"
                    x-model="searchTerm"
                    @input.debounce.300ms="search"
                    placeholder="Search students or drivers"
                    class="w-72 rounded border border-slate-300 px-3 py-2 focus:border-blue-500 focus:outline-none"
                >
                <button
                    type="button"
                    @click="search"
                    class="rounded bg-blue-600 px-4 py-2 text-white hover:bg-blue-700"
                >
                    Search
                </button>
            </div>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <div class="space-y-3">
                <template x-for="student in students" :key="student.id">
                    <div
                        class="rounded border p-4 shadow-sm transition-all"
                        :class="{
                            'border-slate-200 bg-white cursor-grab': !student.inQueue,
                            'border-slate-300 bg-slate-100 opacity-60 cursor-not-allowed': student.inQueue
                        }"
                        :draggable="!student.inQueue"
                        @dragstart="dragStudent(student)"
                    >
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="font-semibold" :class="student.inQueue ? 'text-slate-500' : 'text-slate-800'" x-text="student.name"></div>
                                <div class="text-xs" :class="student.inQueue ? 'text-slate-400' : 'text-slate-500'">
                                    Grade: <span x-text="student.grade ?? 'N/A'"></span>
                                    <span x-show="student.inQueue" class="ml-2 text-amber-600">(Already in queue)</span>
                                </div>
                            </div>
                            <span class="rounded-full px-3 py-1 text-xs" 
                                :class="student.inQueue ? 'bg-slate-200 text-slate-500' : 'bg-slate-100 text-slate-600'">
                                Student
                            </span>
                        </div>
                        <div class="mt-2 text-xs" :class="student.inQueue ? 'text-slate-400' : 'text-slate-500'">
                            Linked Drivers:
                            <span x-text="student.drivers.map(driver => driver.name).join(', ') || 'None'"></span>
                        </div>
                    </div>
                </template>

                <template x-for="driver in drivers" :key="driver.id">
                    <div
                        class="rounded border border-amber-200 bg-amber-50 p-4 shadow-sm"
                        draggable="true"
                        @dragstart="dragDriver(driver)"
                    >
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="font-semibold text-amber-900" x-text="driver.name"></div>
                                <div class="text-xs text-amber-700" x-text="driver.vehicle_desc ?? 'Vehicle unknown'"></div>
                            </div>
                            <span class="rounded-full bg-amber-100 px-3 py-1 text-xs text-amber-700">Driver</span>
                        </div>
                        <div class="mt-2 text-xs text-amber-700">Tag: <span x-text="driver.tag_uid ?? 'Needs assignment'"></span></div>
                    </div>
                </template>
            </div>

            <div class="grid gap-3" style="grid-template-columns: repeat({{ min(count($lanes), 2) }}, minmax(0, 1fr));">
                <template x-for="lane in laneColumns" :key="lane">
                    <div
                        class="flex h-full flex-col rounded-xl border-2 border-dashed border-slate-300 bg-white p-4"
                        @dragover.prevent="handleDragOver($event, lane)"
                        @drop.prevent="handleDrop($event, lane)"
                    >
                        <div class="mb-4 flex items-center justify-between">
                            <div class="text-lg font-semibold text-blue-600">Lane <span x-text="lane"></span></div>
                            <div class="text-xs text-slate-500">Drop cards here or between entries</div>
                        </div>
                        <div class="flex-1 space-y-1">
                            <!-- Drop zone at the top of the lane (position 0) -->
                            <div
                                class="min-h-[40px] rounded border-2 border-dashed transition-all flex items-center justify-center"
                                :class="{ 
                                    'bg-blue-100 border-blue-400': dragOverLane === lane && dragOverIndex === 0,
                                    'bg-slate-50 border-slate-200 hover:bg-slate-100 hover:border-slate-300': !(dragOverLane === lane && dragOverIndex === 0)
                                }"
                                @dragover.prevent="handleDragOverEntry($event, lane, 0)"
                                @drop.prevent="handleDropEntry($event, lane, 0)"
                            >
                                <div class="text-xs text-slate-500 text-center" 
                                    :class="dragOverLane === lane && dragOverIndex === 0 ? 'text-blue-600 font-medium' : ''">
                                    <span x-show="dragOverLane === lane && dragOverIndex === 0">⬇ Drop here</span>
                                    <span x-show="!(dragOverLane === lane && dragOverIndex === 0)">Drop zone</span>
                                </div>
                            </div>
                            
                            <template x-for="(entry, index) in laneEntries[lane]" :key="entry.id">
                                <div>
                                    <!-- The actual entry card -->
                                    <div 
                                        class="rounded border border-blue-200 bg-blue-50 p-4 text-sm text-blue-900 mb-1"
                                    >
                                        <div class="flex items-center justify-between mb-1">
                                            <div class="font-semibold" x-text="entry.driver.name"></div>
                                            <div class="text-xs font-medium text-blue-600">Position <span x-text="index + 1"></span></div>
                                        </div>
                                        <div class="text-xs text-blue-700">
                                            Students:
                                            <span x-text="entry.students.map(student => student.name).join(', ') || 'None'"></span>
                                        </div>
                                    </div>
                                    
                                    <!-- Drop zone after each entry -->
                                    <div
                                        class="min-h-[40px] rounded border-2 border-dashed transition-all flex items-center justify-center"
                                        :class="{ 
                                            'bg-blue-100 border-blue-400': dragOverLane === lane && dragOverIndex === index + 1,
                                            'bg-slate-50 border-slate-200 hover:bg-slate-100 hover:border-slate-300': !(dragOverLane === lane && dragOverIndex === index + 1)
                                        }"
                                        @dragover.prevent="handleDragOverEntry($event, lane, index + 1)"
                                        @drop.prevent="handleDropEntry($event, lane, index + 1)"
                                    >
                                        <div class="text-xs text-slate-500 text-center" 
                                            :class="dragOverLane === lane && dragOverIndex === index + 1 ? 'text-blue-600 font-medium' : ''">
                                            <span x-show="dragOverLane === lane && dragOverIndex === index + 1">⬇ Drop here</span>
                                            <span x-show="!(dragOverLane === lane && dragOverIndex === index + 1)">Drop zone</span>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('mobileEntry', ({ lanes, schoolId, initialLaneEntries = {} }) => ({
                searchTerm: '',
                students: [],
                drivers: [],
                laneColumns: lanes,
                draggedStudent: null,
                draggedDriver: null,
                dragOverLane: null,
                dragOverIndex: null,
                laneEntries: lanes.reduce((acc, lane) => ({ 
                    ...acc, 
                    [lane]: initialLaneEntries[lane] || [] 
                }), {}),

                init() {
                    // Subscribe to WebSocket updates
                    if (window.Echo) {
                        window.Echo.channel(`school.${schoolId}`)
                            .listen('QueueUpdated', () => this.refreshLaneEntries())
                            .listen('CheckinCreated', () => this.refreshLaneEntries())
                            .listen('CallUpdated', () => this.refreshLaneEntries());
                    }
                },

                async refreshLaneEntries() {
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
                            // Update lane entries from queue data
                            // This will renumber positions based on order in the array
                            const updatedEntries = {};
                            response.data.lanes.forEach((lane) => {
                                updatedEntries[lane.number] = (lane.checkins || []).map((checkin) => ({
                                    id: checkin.id,
                                    position: checkin.position, // Store actual position for reference
                                    driver: {
                                        id: checkin.driver_id || null,
                                        name: checkin.driver,
                                    },
                                    students: (checkin.students || []).map((name, idx) => ({
                                        id: idx,
                                        name: name,
                                    })),
                                }));
                            });
                            this.laneEntries = updatedEntries;
                        }
                    } catch (error) {
                        console.error('Failed to refresh lane entries', error);
                    }
                },

                async search() {
                    if (this.searchTerm.length < 2) {
                        return;
                    }

                    try {
                        const response = await window.axios.post('{{ route('mobile.entry.search') }}', {
                            term: this.searchTerm,
                        });

                        this.students = response.data.students ?? [];
                        this.drivers = response.data.drivers ?? [];
                    } catch (error) {
                        console.error('Search failed', error);
                    }
                },

                dragStudent(student) {
                    if (student.inQueue) {
                        return; // Prevent dragging students already in queue
                    }
                    this.draggedStudent = student;
                },

                dragDriver(driver) {
                    this.draggedDriver = driver;
                },

                handleDragOver(event, lane) {
                    event.preventDefault();
                    // Default to appending at the end (null position)
                    this.dragOverLane = lane;
                    this.dragOverIndex = this.laneEntries[lane]?.length ?? 0;
                },

                handleDragOverEntry(event, lane, index) {
                    event.preventDefault();
                    event.stopPropagation();
                    this.dragOverLane = lane;
                    this.dragOverIndex = index;
                },

                async handleDrop(event, lane) {
                    event.preventDefault();
                    // If dropped on the lane container, append to the end
                    await this.dropAtPosition(lane, null);
                    this.resetDragOver();
                },

                async handleDropEntry(event, lane, index) {
                    event.preventDefault();
                    event.stopPropagation();
                    await this.dropAtPosition(lane, index);
                    this.resetDragOver();
                },

                async dropAtPosition(lane, position) {
                    if (!this.draggedDriver && !this.draggedStudent) {
                        return;
                    }

                    // Prevent dropping students already in queue
                    if (this.draggedStudent?.inQueue) {
                        alert('This student is already in the queue.');
                        this.resetDrag();
                        return;
                    }

                    let driver = this.draggedDriver;
                    let studentIds = [];

                    if (!driver && this.draggedStudent) {
                        driver = this.draggedStudent.drivers?.[0];
                        studentIds = [this.draggedStudent.id];
                    } else if (driver && this.draggedStudent) {
                        studentIds = [this.draggedStudent.id];
                    }

                    if (!driver) {
                        alert('Select or drag a driver with an assigned tag.');
                        this.resetDrag();
                        return;
                    }

                    // Convert frontend index (0-based) to database position (1-based)
                    // position 0 = insert at beginning (position 1)
                    // position 1 = insert after first entry (position 2)
                    // position null = append to end
                    const dbPosition = position !== null ? position + 1 : null;

                    // Get all students linked to this driver from search results
                    // This will be updated when the server responds with the actual linked students
                    let studentsForDisplay = [];
                    
                    // First, try to get students from the dragged student's driver relationship
                    if (this.draggedStudent && this.draggedStudent.drivers) {
                        const selectedDriver = this.draggedStudent.drivers.find(d => d.id === driver.id);
                        if (selectedDriver) {
                            // Add the dragged student
                            studentsForDisplay.push({
                                id: this.draggedStudent.id,
                                name: this.draggedStudent.name,
                            });
                        }
                    }
                    
                    // Also include any explicitly provided student IDs
                    studentIds.forEach(id => {
                        const student = this.students.find(s => s.id === id);
                        if (student && !studentsForDisplay.find(s => s.id === id)) {
                            studentsForDisplay.push({
                                id: student.id,
                                name: student.name,
                            });
                        }
                    });
                    
                    // If we're dropping just a driver (no student), we'll show a placeholder
                    // The server will return the actual linked students
                    if (studentsForDisplay.length === 0 && !this.draggedStudent) {
                        studentsForDisplay.push({
                            id: null,
                            name: 'Loading students...',
                        });
                    }

                    // Create optimistic entry for immediate visual feedback
                    const tempId = 'temp-' + Date.now();
                    const optimisticEntry = {
                        id: tempId,
                        driver: {
                            id: driver.id,
                            name: driver.name,
                        },
                        students: studentsForDisplay,
                    };

                    // Insert the entry at the specified position for immediate feedback
                    if (position !== null && position < this.laneEntries[lane].length) {
                        this.laneEntries[lane].splice(position, 0, optimisticEntry);
                    } else {
                        // Append to end
                        this.laneEntries[lane].push(optimisticEntry);
                    }

                    // Mark student as in queue immediately
                    if (this.draggedStudent) {
                        this.draggedStudent.inQueue = true;
                    }

                    // Remove dragged student from search results
                    if (this.draggedStudent) {
                        const studentIndex = this.students.findIndex(s => s.id === this.draggedStudent.id);
                        if (studentIndex !== -1) {
                            this.students.splice(studentIndex, 1);
                        }
                    }

                    try {
                        const response = await window.axios.post('{{ route('mobile.entry.store') }}', {
                            driver_id: driver.id,
                            student_ids: studentIds,
                            lane,
                            position: dbPosition,
                            school_id: this.schoolId,
                        });

                        // Replace temporary entry with real one from server
                        await this.refreshLaneEntries();
                    } catch (error) {
                        console.error('Unable to create checkin', error);
                        
                        // Revert optimistic update on error
                        const tempIndex = this.laneEntries[lane].findIndex(e => e.id === tempId);
                        if (tempIndex !== -1) {
                            this.laneEntries[lane].splice(tempIndex, 1);
                        }
                        
                        // Restore student in search results
                        if (this.draggedStudent) {
                            this.draggedStudent.inQueue = false;
                            this.students.push(this.draggedStudent);
                        }
                        
                        alert('Unable to create checkin. See console for details.');
                    } finally {
                        this.resetDrag();
                    }
                },

                resetDragOver() {
                    this.dragOverLane = null;
                    this.dragOverIndex = null;
                },

                resetDrag() {
                    this.draggedDriver = null;
                    this.draggedStudent = null;
                },
            }));
        });
    </script>
@endsection

