<?php

namespace App\Support;

class EClassUi
{
    public static function initials(?string $name): string
    {
        $tokens = collect(preg_split('/\s+/', trim((string) $name)) ?: [])
            ->filter()
            ->take(2)
            ->map(fn (string $token) => strtoupper(substr($token, 0, 1)));

        return $tokens->isNotEmpty() ? $tokens->implode('') : 'EC';
    }

    public static function roleLabel(?string $role): string
    {
        return match ($role) {
            'teacher' => 'Teacher',
            'admin' => 'Administrator',
            default => 'Student',
        };
    }

    public static function performanceLabel(float|int $percentage): string
    {
        $value = (float) $percentage;

        if ($value >= 90) {
            return 'Excellent';
        }

        if ($value >= 85) {
            return 'Very Good';
        }

        if ($value >= 80) {
            return 'Good';
        }

        return 'Needs Support';
    }

    public static function performanceTone(float|int $percentage): string
    {
        return match (self::performanceLabel($percentage)) {
            'Excellent' => 'excellent',
            'Very Good' => 'good',
            'Good' => 'fair',
            default => 'needs-work',
        };
    }

    public static function attendanceTone(?string $status): string
    {
        return match (strtolower((string) $status)) {
            'late' => 'fair',
            'absent' => 'needs-work',
            default => 'excellent',
        };
    }

    public static function attendanceStatusLabel(?string $status): string
    {
        return match (strtolower((string) $status)) {
            'late' => 'Late',
            'absent' => 'Absent',
            default => 'Present',
        };
    }
}
