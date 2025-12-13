<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User; // 👈 مهم جداً
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Apartment extends Model
{
    use HasFactory;

    // الحقول المسموح تعبئتها
    protected $fillable = [
        'user_id',
        'state',
        'city',
        'street',
        'building_number',
        'rooms',
        'floor',
        'area',
        'has_furnish',
        'price',
        'description',
    ];
      public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * الحجوزات الخاصة بهذه الشقة
     */
    public function bookings()
    {
        return $this->hasMany(Booking::class, 'apartment_id');
    }
    public function favoritedByUsers()
{
    return $this->belongsToMany(User::class, 'favorites', 'apartment_id', 'user_id')
                ->withTimestamps();
}
public function ratings()
{
    return $this->hasMany(Rating::class, 'apartment_id');
}

public function ratedByUsers()
{
    return $this->belongsToMany(User::class, 'ratings', 'apartment_id', 'user_id')
                ->withPivot('rating','comment')
                ->withTimestamps();
}
public function images()
{
    return $this->hasMany(ApartmentImage::class);
}
    protected $casts = [
        'rooms' => 'integer',
        'floor' => 'integer',
        'rent'  => 'decimal:2',
    ];

    // علاقة many-to-many مع المستخدمين
    
}
