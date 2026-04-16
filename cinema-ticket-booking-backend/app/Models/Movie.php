<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Movie extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'synopsis',
        'director',
        'duration',
        'release_date',
        'rating',
        'poster',
        'banner',
        'trailer_url',
        'genres',
        'status',
    ];

    protected $casts = [
        'genres' => 'array',
        'release_date' => 'date',
        'rating' => 'float',
    ];

    public function showtimes()
    {
        return $this->hasMany(Showtime::class);
    }
}
