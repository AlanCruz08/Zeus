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
        try {
            $response = $this->client->get($this->username . '/feeds/ubicacion');
            $ubicacion = json_decode($response->getBody()->getContents(), true);
            
            $filteredFeed = [
                'username' => $ubicacion['username'],
                'name' => $ubicacion['name'],
                'last_value' => $ubicacion['last_value'],
            ];

            $registro = Registro::create([
                'valor' => $filteredFeed['last_value'],
                'unidades' => 'LtLg',
                'sensor_id' => 1,
                'dispositivo_id' => $coche_id
            ]);

            if (!$registro) {
                return response()->json([
                    'msg' => 'Error al guardar registro!',
                    'data' => $registro,
                    'status' => 500
                ], 500);
            }

            $ubicacionDividida = explode(',', $filteredFeed['last_value']);
            $latitud = $ubicacionDividida[0];
            $longitud = $ubicacionDividida[1];

            return response()->json([
                'msg' => 'Registros recuperados con exito!',
                'data' => [
                    'latitud' => $latitud,
                    'longitud' => $longitud
                ],
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
                if ($lastRegistro && $lastRegistro->valor == $filteredFeed['last_value']) {
                    $registro = $lastRegistro;
                } else {
                    $registroNew = Registro::create([
                        'valor' => $filteredFeed['last_value'],
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
            $response = $this->client->get($this->username . '/feeds/distancia/data');
            $reporte = json_decode($response->getBody()->getContents(), true);

            
            foreach ($reporte as $item) {
                $create_at = new DateTime($item['created_at']);

                $existingRecord = Registro::where('valor', $item['value'])
                              ->where('created_at', $create_at->format('Y-m-d H:i:s'))
                              ->first();

                if (!$existingRecord) {
                    $registro = new Registro();
                    $registro->valor = $item['value'];
                    $registro->unidades = 'cm';
                    $registro->created_at = $create_at->format('Y-m-d H:i:s');
                    $registro->sensor_id = 1;
                    $registro->save();
                }

            }

            $registros = Registro::where('valor', '<=', 10)
                            ->where('unidades', 'cm')
                            ->get();
            
            if ($registros->isEmpty()) {
                return response()->json([
                    'msg' => 'No hay registros para mostrar!',
                    'status' => 404
                ], 404);
            }

            foreach ($registros as $registro) {
                $created_at_format =  $registro->created_at->format('Y-m-d, H:i:s');
                $created_at_parts = explode(',', $created_at_format);
                $date = $created_at_parts[0];
                $time = $created_at_parts[1];

                $filtered_data[] = [
                    'valor' => $registro['valor'],
                    'unidades' => $registro['unidades'],
                    'fecha' => $date,
                    'hora' => $time
                ];
            }

            return response()->json([
                'msg' => 'Registros recuperados con exito!',
                'data' => $filtered_data,
                'status' => 200
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'msg' => 'Error al recuperar registros!',
                'error' => $e->getMessage(),
                'status' => 500
            ], 500);
        }
    }
}
