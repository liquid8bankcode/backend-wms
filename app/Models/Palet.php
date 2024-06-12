<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Palet extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    public function paletProducts(){
        return $this->hasMany(PaletProduct::class);
    }
}
