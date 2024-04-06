<?php

namespace App\Http\Controllers;

use App\Models\Coche;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Sensor;

class CocheController extends Controller
{
    protected $reglasCrear = [
        'alias' => 'required|string|max:60',
        'descripcion' => 'required|string|max:255',
        'user_id' => 'required|integer',
    ];

    public function index()
    {
        $coches = Coche::all();
        return response()->json([
            'coches' => $coches,
            'status' => '200'
        ], 200);
    }

    public function store(Request $request)
    {   
        $validacion = Validator::make($request->all(), $this->reglasCrear);

        if ($validacion->fails())
            return response()->json([
                'msg' => 'Error en las validaciones',
                'data' => $validacion->errors(),
                'status' => '422'
            ], 422);
        
        $codigo_ran = rand(100000, 999999);

        $coche = Coche::create([
            'alias' => $request->alias,
            'descripcion' => $request->descripcion,
            'user_id' => $request->user_id,
            'codigo' => $codigo_ran
        ]);

        $sensor = Sensor::create([
            'coche_id' => $coche->id,
            'key'      => 'gps',
            
        ]);

        if (!$coche)
            return response()->json([
                'msg' => 'Error al crear el coche',
                'data' => 'error',
                'status' => '500'
            ], 500);

        return response()->json([
            'msg' => 'Coche creado correctamente',
            'data' => $coche,
            'status' => '201'
        ], 201);
    }

    public function show($user_id)
    {
        $coches = Coche::where('user_id', $user_id)
                        ->select('id', 'alias', 'descripcion')
                        ->get();
        if(!$coches)
            return response()->json([
                'msg' => 'No se encontraron coches',
                'data' => 'error',
                'status' => '404'
            ], 404);
        
        return response()->json([
            'msg' => 'Coches encontrados',
            'data' => $coches,
            'status' => '200'
        ], 200);
    }

    public function showAll($user_id)
    {
        $coches = Coche::with('sensors')->where('user_id', $user_id)->get();
        if(!$coches)
            return response()->json([
                'msg' => 'No se encontraron coches',
                'data' => 'error',
                'status' => '404'
            ], 404);

        return response()->json([
            'coches' => $coches,
            'status' => '200'
        ], 200);
    }

    public function ubicacion($coche_id)
    {
        $coche = Coche::find($coche_id);
        if(!$coche)
            return response()->json([
                'msg' => 'No se encontró el coche',
                'data' => 'error',
                'status' => '404'
            ], 404);

        return response()->json([
            'ubicacion' => $coche->ubicacion,
            'status' => '200'
        ], 200);
    }

}