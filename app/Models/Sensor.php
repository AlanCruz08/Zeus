<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sensor extends Model
{
    use HasFactory;
    protected $table = 'sensors';
    protected $primaryKey = 'id';
    protected $fillable = ['key', 'descripcion', 'coche_id'];

    public function coche()
    {
        return $this->belongsTo(Coche::class, 'coche_id', 'id');
    }

    public function registros()
    {
        return $this->hasMany(Registro::class, 'sensor_id', 'id');
    }
}
