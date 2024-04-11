<?php

namespace App\Http\Controllers;

use App\Models\Registro;
use App\Models\Sensor;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use App\Models\Coche;
use DateTime;

class RegistroController extends Controller
{
    private $key;
    private $username;
    private $api;
    private $client;

    public function __construct()
    {
        $this->key = env('AIO_KEY');
        $this->username = env('AIO_USERNAME');
        $this->api = env('AIO_API');

        $this->client = new Client([
            'base_uri' => $this->api,
            'headers' => [
                'X-AIO-Key' => $this->key,
            ],
            'verify' => false,
        ]);
    }
    public function ubicacion(int $coche_id)
    {
        $coche = Coche::find($coche_id);
        if(!$coche) {
            return response()->json([
                'msg' => 'Coche no encontrado',
                'status' => 404
            ], 404);
        }

        $sensor_id = Sensor::where('coche_id', $coche_id)
                            ->where('sku', 'LIKE', 'GPS%')
                            ->first();

        if ($sensor_id) {
            $registro = Registro::where('sensor_id', $sensor_id->id)
                                ->orderBy('created_at', 'desc')
                                ->first();
            if ($registro) {
                $ubicacionDividida = explode(',', $registro->valor);
                $latitud = $ubicacionDividida[0];
                $longitud = $ubicacionDividida[1];
            } else {
                $latitud = "0";
                $longitud = "0";
            }
        } else {
            $latitud = "0";
            $longitud = "0";
        }

        return response()->json([
            'msg' => 'Registros recuperados con exito!',
            'data' => [
                'latitud' => $latitud,
                'longitud' => $longitud
            ],
            'status' => 200
        ], 200);
    }

    public function ledControl(int $coche_id) 
    {
        $coche = Coche::find($coche_id);
        if(!$coche) {
            return response()->json([
                'msg' => 'Coche no encontrado',
                'status' => 404
            ], 404);
        }
        try {
            $response = $this->client->get($this->username . '/feeds/ledcontrol');
            $response_json = json_decode($response->getBody()->getContents(), true);
            
            $filteredFeed = [
                'username' => $response_json['username'],
                'name' => $response_json['name'],
                'last_value' => $response_json['last_value'],
            ];

            $sensor_id = Sensor::where('coche_id', $coche_id)
                                ->where('sku', 'LIKE', 'LED%')
                                ->first();

            if ($sensor_id) {
                $lastRegistro = Registro::where('sensor_id', $sensor_id->id)
                                    ->orderBy('created_at', 'desc')
                                    ->first();
                if ($lastRegistro && $lastRegistro->valor == $filteredFeed['last_value']) {
                    $registro = $lastRegistro->valor;
                } else {
                    $registroNew = Registro::create([
                        'valor' => $filteredFeed['last_value'],
                        'unidades' => '0/1',
                        'sensor_id' => $sensor_id->id,
                    ]);
                    $registro = $registroNew->valor;
                }
            } else {
                $registro = "0";
            }

            return response()->json([
                'msg' => 'Registros recuperados con exito!',
                'data' => $registro,
                'status' => 200
            ], $response->getStatusCode());
        } catch (\Exception $e) {
            return response()->json([
                'msg' => 'Error al recuperar registros!',
                'error' => $e->getMessage(),
                'status' => 500
            ], 500);
        }
    }

    public function ledControlPost(int $coche_id, Request $request) 
    {
        $coche = Coche::find($coche_id);
        if(!$coche) {
            return response()->json([
                'msg' => 'Coche no encontrado',
                'status' => 404
            ], 404);
        }
        try {
            $response = $this->client->post($this->username . '/feeds/ledcontrol/data', [
                'json' => [
                    'value' => $request->value
                ]
            ]);
            $respuesta = json_decode($response->getBody()->getContents(), true);

            $filteredFeed = [
                'value' => $respuesta['value'],
                'feedkey' => $respuesta['feed_key'],
            ];

            $sensor_id = Sensor::where('coche_id', $coche_id)
                                ->where('sku', 'LIKE', 'LED%')
                                ->first();
            
            if ($sensor_id) {
                $lastRegistro = Registro::where('sensor_id', $sensor_id->id)
                                    ->orderBy('created_at', 'desc')
                                    ->first();
                if ($lastRegistro && $lastRegistro->valor == $filteredFeed['value']) {
                    $registro = $lastRegistro;
                } else {
                    $registroNew = Registro::create([
                        'valor' => $filteredFeed['value'],
                        'unidades' => '0/1',
                        'sensor_id' => $sensor_id->id,
                    ]);
                    $registro = $registroNew;
                }
            } else {
                $registro = Registro::where('unidades', '0/1')
                                ->first();
            }

            return response()->json([
                'msg' => 'Registros hecho con exito!',
                'data' => $registro,
                'status' => 200
            ], $response->getStatusCode());

        } catch (\Exception $e) {
            return response()->json([
                'msg' => 'Error al recuperar registros!',
                'error' => $e->getMessage(),
                'status' => 500
            ], 500);
        }
    }

    public function reporte(int $coche_id)
    {
        $coche = Coche::find($coche_id);
        if(!$coche) {
            return response()->json([
                'msg' => 'Coche no encontrado',
                'status' => 404
            ], 404);
        }
        try {
            // Obtener los registros del coche con valor menor de 10
            $sensor_id = Sensor::where('coche_id', $coche_id)
                                ->where('sku', 'LIKE', 'DIS%')
                                ->first();

            $registros = Registro::whereHas('sensor', function ($query) use ($coche_id) {
                $query->where('coche_id', $coche_id);
            })->where('valor', '<', 10)
            ->where('sensor_id', $sensor_id->id)
            ->select('valor', 'unidades', 'created_at')
            ->get()
            ->map(function ($registro) {
                return [
                    'valor' => $registro->valor,
                    'unidades' => $registro->unidades,
                    'sensor_id' => $registro->sensor_id,
                    'fecha' => $registro->created_at->format('Y-m-d'),
                    'hora' => $registro->created_at->format('H:i:s')
                ];
            });
    
            return response()->json([
                'msg' => 'Registros recuperados con éxito!',
                'data' => $registros,
                'status' => 200
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'msg' => 'Error al recuperar registros!',
                'error' => $e->getMessage(),
                'status' => 500
            ], 500);
        }
        
    }
}
