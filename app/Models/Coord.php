<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coord extends Model
{
    use HasFactory;

    protected $fillable = ['latitude', 'longitude', 'polygon_id'];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    public function polygon()
    {
        return $this->belongsTo(Polygon::class);
    }
}
