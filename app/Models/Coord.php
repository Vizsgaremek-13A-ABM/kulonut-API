<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coord extends Model
{
    use HasFactory;

    protected $fillable = ['x', 'y', 'polygon_id'];

    public function polygon()
    {
        return $this->belongsTo(Polygon::class);
    }
}
