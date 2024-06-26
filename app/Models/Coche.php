<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coche extends Model
{
    use HasFactory;
    protected $table = 'coches';
    protected $primaryKey = 'id';
    protected $fillable = ['alias', 'descripcion', 'codigo', 'user_id'];

    public function users()
    {
        return $this->belongsTo(User::class, 'user_coches', 'coche_id', 'user_id');
    }

    public function sensors()
    {
        return $this->hasMany(Sensor::class, 'coche_id', 'id');
    }
}
