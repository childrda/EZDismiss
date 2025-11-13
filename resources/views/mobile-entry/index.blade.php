@extends('layouts.app')

@section('content')
    <div
        x-data="mobileEntry({
            lanes: @json($lanes),
            schoolId: {{ $school->id }},
        })"
        class="space-y-6"
    >
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-slate-800">Mobile Entry</h1>
                <p class="text-sm text-slate-500">Search students or drivers, drag into lane columns to queue manually.</p>
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
                        class="rounded border border-slate-200 bg-white p-4 shadow-sm"
                        draggable="true"
                        @dragstart="dragStudent(student)"
                    >
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="font-semibold text-slate-800" x-text="student.name"></div>
                                <div class="text-xs text-slate-500">Grade: <span x-text="student.grade ?? 'N/A'"></span></div>
                            </div>
                            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs text-slate-600">Student</span>
                        </div>
                        <div class="mt-2 text-xs text-slate-500">
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
                        @dragover.prevent
                        @drop="dropOnLane(lane)"
                    >
                        <div class="mb-4 flex items-center justify-between">
                            <div class="text-lg font-semibold text-blue-600">Lane <span x-text="lane"></span></div>
                            <div class="text-xs text-slate-500">Drop cards here</div>
                        </div>
                        <div class="flex-1 space-y-3">
                            <template x-for="entry in laneEntries[lane]" :key="entry.id">
                                <div class="rounded border border-blue-200 bg-blue-50 p-4 text-sm text-blue-900">
                                    <div class="font-semibold" x-text="entry.driver.name"></div>
                                    <div class="text-xs text-blue-700">
                                        Students:
                                        <span x-text="entry.students.map(student => student.name).join(', ') || 'None'"></span>
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
            Alpine.data('mobileEntry', ({ lanes }) => ({
                searchTerm: '',
                students: [],
                drivers: [],
                laneColumns: lanes,
                draggedStudent: null,
                draggedDriver: null,
                laneEntries: lanes.reduce((acc, lane) => ({ ...acc, [lane]: [] }), {}),

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
                    this.draggedStudent = student;
                },

                dragDriver(driver) {
                    this.draggedDriver = driver;
                },

                async dropOnLane(lane) {
                    if (!this.draggedDriver && !this.draggedStudent) {
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

                    try {
                        const response = await window.axios.post('{{ route('mobile.entry.store') }}', {
                            driver_id: driver.id,
                            student_ids: studentIds,
                            lane,
                        });

                        this.laneEntries[lane].push({
                            id: response.data.checkin_id,
                            driver,
                            students: this.students.filter(s => studentIds.includes(s.id)),
                        });
                    } catch (error) {
                        console.error('Unable to create checkin', error);
                        alert('Unable to create checkin. See console for details.');
                    } finally {
                        this.resetDrag();
                    }
                },

                resetDrag() {
                    this.draggedDriver = null;
                    this.draggedStudent = null;
                },
            }));
        });
    </script>
@endsection

