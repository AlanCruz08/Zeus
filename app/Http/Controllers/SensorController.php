<?php

namespace App\Http\Controllers;

use App\Models\Sensor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SensorController extends Controller
{
    protected $reglasCrear = [
        'key' => 'required|string|max:60',
        'descripcion' => 'required|string|max:255',
        'coche_id' => 'required|integer'
    ];
    public function index()
    {
        $sensors = Sensor::all();
        return response()->json([
            'sensors'=>$sensors,
            'status'=>'200'
        ],200);
    }

    public function create(Request $request)
    {
        $validacion = Validator::make($request->all(), $this->reglasCrear);

        if ($validacion->fails())
            return response()->json([
                'msg' => 'Error en las validaciones',
                'data' => $validacion->errors(),
                'status' => '422'
            ], 422);

        $sensor = Sensor::create($request->all());

        return response()->json([
            'msg' => 'Sensor creado correctamente',
            'data' => $sensor,
            'status' => '201'
        ], 201);

    }
}
