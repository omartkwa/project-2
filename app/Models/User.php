<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
    'first_name' ,
    'last_name'  ,
    'role'        ,
    'birthdate'  ,
    'mobile'    ,
    'password'  ,
    'profile_photo' ,
    'id_photo'     ,
    'address'    ,
    'card_type'    ,
    'card_number'  ,
    'security_code' ,
   'expiry_date'  
           
    ];
   public function apartments()
    {
        return $this->hasMany(Apartment::class, 'user_id');
    }

    /**
     * الحجوزات التي قام بها هذا المستخدم كمستأجر
     */
    public function bookings()
    {
        return $this->hasMany(Booking::class, 'user_id');
    }
    public function favoriteApartments()
{
    return $this->belongsToMany(Apartment::class, 'favorites', 'user_id', 'apartment_id')
                ->withTimestamps();
}
public function ratings()
{
    return $this->hasMany(Rating::class, 'user_id');
}

public function ratedApartments()
{
    return $this->belongsToMany(Apartment::class, 'ratings', 'user_id', 'apartment_id')
                ->withPivot('rating','comment')
                ->withTimestamps();
}

    protected $hidden = [
   
        'password',
        'remember_token',
    ];
  
 
    protected function casts(): array
    {
        return [
      
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];}
    
}
