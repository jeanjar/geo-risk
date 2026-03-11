<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $fillable = [
        'external_id',
        'type',
        'lat',
        'lng',
        'location',
        'description',
        'severity',
        'source',
        'timestamp',
    ];

    protected function casts(): array
    {
        return [
            'lat' => 'float',
            'lng' => 'float',
            'severity' => 'float',
            'timestamp' => 'datetime',
        ];
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeWithinBbox($query, float $minLng, float $minLat, float $maxLng, float $maxLat)
    {
        return $query->whereBetween('lng', [$minLng, $maxLng])
            ->whereBetween('lat', [$minLat, $maxLat]);
    }
}
