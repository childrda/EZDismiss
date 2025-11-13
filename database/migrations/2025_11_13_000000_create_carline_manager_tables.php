<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('schools', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('address')->nullable();
            $table->string('phone')->nullable();
            $table->unsignedTinyInteger('lane_count')->default(1);
            $table->enum('lane_color_mode', ['global', 'per_lane'])->default('per_lane');
            $table->enum('default_lane_behavior', ['manual', 'round_robin', 'rfid_based'])->default('manual');
            $table->string('timezone')->nullable();
            $table->time('pickup_start_time')->nullable();
            $table->time('pickup_end_time')->nullable();
            $table->string('logo_path')->nullable();
            $table->string('primary_color')->nullable();
            $table->string('api_key')->nullable()->unique();
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('school_id')->nullable()->after('id')->constrained('schools');
            $table->enum('role', ['district_admin', 'school_admin', 'teacher', 'staff'])->default('staff')->after('password');
            $table->softDeletes();
            $table->index(['school_id', 'role']);
        });

        Schema::create('homerooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('name');
            $table->string('teacher_name')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['school_id', 'name']);
        });

        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('powerschool_id')->nullable()->index();
            $table->string('name');
            $table->string('grade')->nullable();
            $table->foreignId('homeroom_id')->nullable()->constrained('homerooms');
            $table->timestamps();
            $table->softDeletes();
            $table->index(['school_id', 'homeroom_id']);
        });

        Schema::create('drivers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('email')->nullable()->index();
            $table->string('vehicle_desc')->nullable();
            $table->string('external_id')->nullable()->index();
            $table->string('tag_uid')->unique();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['school_id', 'tag_uid']);
        });

        Schema::create('authorized_pickups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('driver_id')->constrained('drivers')->cascadeOnDelete();
            $table->string('relationship')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['student_id', 'driver_id']);
            $table->index(['school_id', 'student_id']);
        });

        Schema::create('rfid_readers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('name');
            $table->unsignedInteger('lane');
            $table->enum('endpoint_type', ['http', 'mqtt', 'tcp'])->default('http');
            $table->string('ip_address')->nullable();
            $table->string('api_key');
            $table->boolean('enabled')->default(true);
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['school_id', 'lane', 'name']);
        });

        Schema::create('checkins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('driver_id')->constrained('drivers')->cascadeOnDelete();
            $table->enum('method', ['rfid', 'manual'])->default('manual');
            $table->unsignedInteger('lane');
            $table->unsignedInteger('position');
            $table->timestamps();
            $table->index(['school_id', 'lane', 'position']);
        });

        Schema::create('calls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('checkin_id')->constrained('checkins')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->enum('status', ['called', 'en_route', 'staged', 'released', 'hold'])->default('called');
            $table->foreignId('by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['school_id', 'status']);
        });

        Schema::create('exceptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->string('reason');
            $table->text('notes')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->nullable()->constrained('schools')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('event_type');
            $table->text('description')->nullable();
            $table->json('context')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('exceptions');
        Schema::dropIfExists('calls');
        Schema::dropIfExists('checkins');
        Schema::dropIfExists('rfid_readers');
        Schema::dropIfExists('authorized_pickups');
        Schema::dropIfExists('drivers');
        Schema::dropIfExists('students');
        Schema::dropIfExists('homerooms');

        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('school_id');
            $table->dropColumn('role');
            $table->dropSoftDeletes();
            $table->dropIndex(['school_id', 'role']);
        });

        Schema::dropIfExists('schools');
    }
};

