<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApartmentImage extends Model
{
 protected $fillable = [
        'apartment_id',
        'image_base64',
    ];

    protected $appends = ['data_uri'];

    public function apartment()
    {
        return $this->belongsTo(Apartment::class);
    }

    // نرجع الصورة بشكل data:image/jpeg;base64,... مهما كان نوعها
    public function getDataUriAttribute()
    {
        return "data:image/jpeg;base64,{$this->image_base64}";
    }
}
