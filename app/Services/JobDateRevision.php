<?php

namespace App\Services;

use App\Models\Job;

/**
 * Exact, explicitly requested *website-administrator* dates for the 27
 * historical job advertisements. These reference dates are never described
 * as their actual original posting date or employer-verified reopenings.
 */
final class JobDateRevision
{
    private const IDS = [
        11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24,
        25, 26, 28, 29, 30, 31, 32, 33, 34, 35, 36, 37, 38,
    ];

    public static function deadlineForId(int $id): ?string
    {
        return match (true) {
            in_array($id, [11,12,13,14,15], true) => '2026-11-27',
            in_array($id, [16,17,18,19,20], true) => '2026-11-28',
            in_array($id, [21,22,23,24,25], true) => '2026-11-29',
            in_array($id, [26,28,29,30], true) => '2026-11-30',
            in_array($id, [31,32,33,34], true) => '2026-12-01',
            in_array($id, [35,36,37,38], true) => '2026-12-02',
            default => null,
        };
    }

    public static function referenceForId(int $id): ?string
    {
        $index = array_search($id, self::IDS, true);
        return $index === false ? null : ['2026-10-14', '2026-10-15', '2026-10-16'][$index % 3];
    }

    public static function applies(Job $job): bool
    {
        $target = self::deadlineForId((int) $job->id);
        return $target !== null
            && substr((string) $job->application_deadline, 0, 10) === $target;
    }

    public static function note(Job $job): ?string
    {
        if (!self::applies($job)) return null;
        return 'The closing date was revised by this website’s administrator. '
            .'Recruitment availability and the revised date have not been independently '
            .'confirmed with the named employer. Please verify directly before applying.';
    }
}
