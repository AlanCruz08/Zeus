<?php

namespace App\Http\Controllers;

use App\Models\Coche;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Sensor;
use Illuminate\Support\Facades\DB;

class CocheController extends Controller
{
    protected $reglasCrear = [
        'alias' => 'required|string|max:60',
        'descripcion' => 'required|string|max:255',
        'codigo' => 'required|string|max:6',
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
        
        $coche = Coche::where('codigo', $request->codigo)->first();
        if(!$coche)
            return response()->json([
                'msg' => 'No existe un coche con ese código',
                'data' => null,
                'status' => '404'
            ], 200);

        $user_coches_exist = DB::table('user_coches')->where('coche_id', $coche->id)->first();
        if($user_coches_exist)
            return response()->json([
                'msg' => 'El coche ya está asignado a un usuario',
                'data' => null,
                'status' => '409'
            ], 209);
        
        $user = User::where('id', $request->user_id)->first();
        if(!$user)
            return response()->json([
                'msg' => 'No existe un usuario con ese id',
                'data' => null,
                'status' => '404'
            ], 200);
        
        $user->coches()->attach($coche->id);

        if (!$user->coches()->find($coche->id))
            return response()->json([
                'msg' => 'Error al crear el coche',
                'data' => null,
                'status' => '500'
            ], 500);

        $coche->alias = $request->alias;
        $coche->descripcion = $request->descripcion;
        
        if (!$coche->save()) {
            $user->coches()->detach($coche->id);
            
            return response()->json([
                'msg' => 'Coche No guardado',
                'data' => null,
                'status' => '500'
            ], 500);
        }

        return response()->json([
            'msg' => 'Coche creado correctamente',
            'data' => $coche,
            'status' => '201'
        ], 201);
    }

    public function show($user_id)
    {
        $coches = Coche::select('coches.id', 'coches.alias', 'coches.descripcion')
                        ->join('user_coches', 'coches.id', '=', 'user_coches.coche_id')
                        ->join('users', 'user_coches.user_id', '=', 'users.id')
                        ->where('users.id', $user_id)
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

    public function showSensors($coche_id)
    {
        $coche = Coche::find($coche_id);
        if(!$coche)
            return response()->json([
                'msg' => 'No se encontró el coche',
                'data' => 'error',
                'status' => '404'
            ], 404);

        $sensors = Sensor::where('coche_id', $coche_id)->get();
        if(!$sensors)
            return response()->json([
                'msg' => 'No se encontraron sensores',
                'data' => 'error',
                'status' => '404'
            ], 404);

        return response()->json([
            'msg' => 'Sensores encontrados',
            'data' => $sensors,
            'status' => '200'
        ], 200);
    }

    public function showAll($user_id)
    {
        $coches = Coche::with('sensors')
                        ->join('user_coches', 'coches.id', '=', 'user_coches.coche_id')
                        ->where('user_coches.user_id', $user_id)->get();

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