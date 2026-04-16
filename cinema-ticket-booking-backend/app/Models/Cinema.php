<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cinema extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'address',
        'format',
        'city',
        'phone',
        'is_active',
    ];

    public function halls(){
        return $this->hasMany(Hall::class);
    }
}
