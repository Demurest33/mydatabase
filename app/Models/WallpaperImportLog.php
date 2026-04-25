<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WallpaperImportLog extends Model
{
    protected $fillable = ['batch_id', 'workshop_id', 'status', 'reason'];

    public static function record(string $batchId, string $workshopId, string $status, string $reason): void
    {
        static::create([
            'batch_id'    => $batchId,
            'workshop_id' => $workshopId,
            'status'      => $status,
            'reason'      => $reason,
        ]);
    }
}
