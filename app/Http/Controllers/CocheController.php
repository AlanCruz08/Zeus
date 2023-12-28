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

    public function show(Coche $coche)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Coche  $coche
     * @return \Illuminate\Http\Response
     */
    public function edit(Coche $coche)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Coche  $coche
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Coche $coche)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Coche  $coche
     * @return \Illuminate\Http\Response
     */
    public function destroy(Coche $coche)
    {
        //
    }
}
