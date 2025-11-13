<?php

namespace App\Support;

use Illuminate\Support\Facades\Auth;

class Tenant
{
    protected static ?int $schoolId = null;

    protected static bool $districtAdmin = false;

    public static function setFromAuth(): void
    {
        $user = Auth::user();

        if (!$user) {
            static::$schoolId = null;
            static::$districtAdmin = false;

            return;
        }

        static::$schoolId = $user->school_id;
        static::$districtAdmin = $user->role === 'district_admin';
    }

    public static function set(?int $schoolId, bool $isDistrictAdmin = false): void
    {
        static::$schoolId = $schoolId;
        static::$districtAdmin = $isDistrictAdmin;
    }

    public static function schoolId(): ?int
    {
        return static::$schoolId;
    }

    public static function isDistrictAdmin(): bool
    {
        return static::$districtAdmin;
    }
}

