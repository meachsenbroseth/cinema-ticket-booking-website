<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Showtime extends Model
{
    protected $fillable = [
        'movie_id',
        'hall_id',
        'show_date',
        'show_time',
        'format',
        'price_standard',
        'price_vip',
        'status',
    ];

    public function movie(){
        return $this->belongsTo(Movie::class);
    }

    public function hall(){
        return $this->belongsTo(Hall::class);
    }

    public function bookings(){
        return $this->hasMany(Booking::class);
    }
}
