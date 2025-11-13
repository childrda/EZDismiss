<?php

namespace App\Models;

use App\Models\Concerns\HasSchoolScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Call extends Model
{
    use HasFactory;
    use HasSchoolScope;

    protected $fillable = [
        'school_id',
        'checkin_id',
        'student_id',
        'status',
        'by_user_id',
    ];

    public function checkin(): BelongsTo
    {
        return $this->belongsTo(Checkin::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'by_user_id');
    }
}

