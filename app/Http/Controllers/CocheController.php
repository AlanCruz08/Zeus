<?php

namespace App\Http\Controllers;

use App\Models\Coche;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CocheController extends Controller
{
    protected $reglasCrear = [
        'alias' => 'required|string|max:60',
        'descripcion' => 'required|string|max:255',
        'codigo' => 'required|integer|max:60',
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

        $coche = Coche::create($request->all());

        return response()->json([
            'msg' => 'Coche creado correctamente',
            'data' => $coche,
            'status' => '201'
        ], 201);
    }

    public function show($user_id)
    {
        $coches = Coche::where('user_id', $user_id)->get();
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

    
}