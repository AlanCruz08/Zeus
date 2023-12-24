<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Registro extends Model
{
    protected $table = 'registros';
    protected $primaryKey = 'id';
    protected $fillable = ['valor', 'unidades', 'sensor_id'];

    public function sensor()
    {
        return $this->belongsTo(Sensor::class, 'sensor_id', 'id');
    }
}
