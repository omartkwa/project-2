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
    'birthdate'  ,
    'mobile'    ,
    'password'  ,
    'profile_photo' ,
    'id_photo'     ,
    'address'    ,
    'card_type'    ,
    'card_number'  ,
    'security_code' ,
   'expiry_date'  ,
   'fcm_token'
           
    ];
   public function apartments()
    {
        return $this->hasMany(Apartment::class, 'user_id');
    }
 public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }
  
    public function bookings()
    {
        return $this->hasMany(Booking::class, 'user_id');
    }

public function ratings()
{
    return $this->hasMany(Rating::class, 'user_id');
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
