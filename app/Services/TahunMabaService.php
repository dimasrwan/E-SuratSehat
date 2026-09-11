<?php

namespace App\Services;

use App\Models\TahunMaba;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TahunMabaService
{
    /**
     * Get active integer year (e.g. 2026).
     * Fallbacks to current year if table empty.
     */
    public static function getActiveYearInt(): int
    {
        $activeModel = TahunMaba::where('is_active', true)->first();
        if ($activeModel) {
            return (int) $activeModel->tahun;
        }

        // Fallback to first available or current system year
        $first = TahunMaba::orderBy('tahun', 'desc')->first();
        return $first ? (int) $first->tahun : (int) date('Y');
    }

    /**
     * Get active TahunMaba model instance.
     */
    public static function getActiveYear(): ?TahunMaba
    {
        return TahunMaba::where('is_active', true)->first();
    }

    /**
     * Atomically set target year as the ONLY active year.
     */
    public static function activateYear(int $tahunOrId, ?User $actor = null): bool
    {
        return DB::transaction(function () use ($tahunOrId, $actor) {
            $target = TahunMaba::where('id', $tahunOrId)->orWhere('tahun', $tahunOrId)->lockForUpdate()->first();
            if (!$target) {
                return false;
            }

            // Set all years to inactive
            TahunMaba::query()->update(['is_active' => false]);

            // Set target year to active
            $target->is_active = true;
            $target->save();

            // Log activity for audit trail
            Log::info('TAHUN_MABA_ACTIVATED', [
                'actor_id' => $actor ? $actor->id : null,
                'actor_email' => $actor ? $actor->email : 'system',
                'tahun_id' => $target->id,
                'tahun' => $target->tahun,
                'nama' => $target->nama,
                'timestamp' => now()->toIso8601String(),
            ]);

            return true;
        }, 3);
    }
}
