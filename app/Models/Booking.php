<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $fillable = [
        'apartment_id',
        'user_id',
        'start_date',
        'end_date',
        'status',
        
    ];
protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'actioned_at' => 'datetime',
    ];

  public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * الشقة المحجوزة — FK: bookings.apartment_id
     */
    public function apartment()
    {
        return $this->belongsTo(Apartment::class, 'apartment_id');
    }
}
