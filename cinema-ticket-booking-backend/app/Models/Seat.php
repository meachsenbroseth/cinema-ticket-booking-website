<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Seat extends Model
{
    protected $fillable = [
        'row',
        'number',
        'seat_code',
        'type',
        'is_active',
    ];

    public function hall()
    {
        return $this->belongsTo(Hall::class);
    }

    public function bookingSeats()
    {
        return $this->hasMany(BookingSeat::class); 
    }
}
