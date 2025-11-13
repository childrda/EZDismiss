<?php

namespace App\Http\Controllers\Api;

use App\Events\CheckinCreated;
use App\Events\QueueUpdated;
use App\Http\Controllers\Controller;
use App\Models\Checkin;
use App\Models\Driver;
use App\Models\RfidReader;
use App\Models\School;
use App\Services\ActivityLogger;
use App\Services\QueueService;
use App\Support\Tenant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class RfidCheckinController extends Controller
{
    public function __construct(
        protected QueueService $queueService,
        protected ActivityLogger $logger,
    ) {
    }

    public function checkinByReader(Request $request): JsonResponse
    {
        $data = $request->validate([
            'reader_id' => ['required', 'integer', 'exists:rfid_readers,id'],
            'tag_uid' => ['required', 'string'],
        ]);

        $reader = RfidReader::with('school')->findOrFail($data['reader_id']);
        $school = $reader->school;

        Tenant::set($school->id);

        if (!$reader->enabled) {
            return response()->json([
                'message' => 'Reader is disabled.',
            ], Response::HTTP_FORBIDDEN);
        }

        $driver = Driver::where('school_id', $school->id)
            ->where('tag_uid', $data['tag_uid'])
            ->first();

        if (!$driver) {
            $this->logger->log('rfid_scan', 'RFID tag not found', [
                'tag_uid' => $data['tag_uid'],
                'reader_id' => $reader->id,
                'lane' => $reader->lane,
                'outcome' => 'not_found',
            ]);

            return response()->json([
                'message' => 'Driver not found for tag.',
            ], Response::HTTP_NOT_FOUND);
        }

        $checkin = $this->createCheckin($school, $driver, $reader->lane, 'rfid', [
            'reader_id' => $reader->id,
        ]);

        $this->logger->log('rfid_scan', 'RFID checkin created', [
            'tag_uid' => $data['tag_uid'],
            'reader_id' => $reader->id,
            'lane' => $reader->lane,
            'driver_id' => $driver->id,
            'checkin_id' => $checkin->id,
            'outcome' => 'success',
        ]);

        return response()->json([
            'message' => 'Checkin recorded.',
            'checkin_id' => $checkin->id,
        ]);
    }

    public function checkinByLane(Request $request, int $lane): JsonResponse
    {
        $data = $request->validate([
            'tag_uid' => ['required', 'string'],
            'school_key' => ['nullable', 'string'],
        ]);

        $school = $this->resolveSchoolFromRequest($request, $data['school_key'] ?? null);

        if (!$school) {
            return response()->json([
                'message' => 'School could not be resolved.',
            ], Response::HTTP_BAD_REQUEST);
        }

        Tenant::set($school->id);

        $driver = Driver::where('school_id', $school->id)
            ->where('tag_uid', $data['tag_uid'])
            ->first();

        if (!$driver) {
            $this->logger->log('rfid_scan', 'RFID tag not found (lane endpoint)', [
                'tag_uid' => $data['tag_uid'],
                'lane' => $lane,
                'outcome' => 'not_found',
            ]);

            return response()->json([
                'message' => 'Driver not found for tag.',
            ], Response::HTTP_NOT_FOUND);
        }

        $checkin = $this->createCheckin($school, $driver, $lane, 'rfid');

        $this->logger->log('rfid_scan', 'RFID lane checkin created', [
            'tag_uid' => $data['tag_uid'],
            'lane' => $lane,
            'driver_id' => $driver->id,
            'checkin_id' => $checkin->id,
            'outcome' => 'success',
        ]);

        return response()->json([
            'message' => 'Checkin recorded.',
            'checkin_id' => $checkin->id,
        ]);
    }

    protected function resolveSchoolFromRequest(Request $request, ?string $schoolKey): ?School
    {
        if ($schoolKey) {
            return School::where('api_key', $schoolKey)->first();
        }

        $user = $request->user();

        if ($user && $user->school_id) {
            return School::find($user->school_id);
        }

        return null;
    }

    protected function createCheckin(School $school, Driver $driver, int $lane, string $method, array $context = []): Checkin
    {
        return DB::transaction(function () use ($school, $driver, $lane, $method, $context): Checkin {
            $position = $this->queueService->nextPosition($school, $lane);

            $checkin = Checkin::create([
                'school_id' => $school->id,
                'driver_id' => $driver->id,
                'method' => $method,
                'lane' => $lane,
                'position' => $position,
            ]);

            CheckinCreated::dispatch($checkin);
            QueueUpdated::dispatch($school, $lane);

            return $checkin;
        });
    }
}

