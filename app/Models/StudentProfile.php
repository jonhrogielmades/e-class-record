<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class StudentProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'section_id',
        'student_number',
        'name',
        'email',
        'guardian',
        'contact',
        'address',
        'focus',
        'status',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function attendanceRecords(): HasMany
    {
        return $this->hasMany(AttendanceRecord::class);
    }

    public function grades(): HasMany
    {
        return $this->hasMany(Grade::class);
    }

    public static function nextStudentNumber(Section $section): string
    {
        $count = static::query()->where('section_id', $section->id)->count() + 1;
        $sectionCode = Str::upper((string) Str::of($section->name)->afterLast(' ')->replaceMatches('/[^A-Za-z0-9]/', ''));

        return sprintf('%s-%s-%03d', now()->year, $sectionCode ?: 'X', $count);
    }
}