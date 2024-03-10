<?php

namespace App\Http\Controllers;

use App\Models\Registro;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use App\Models\Coche;

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
                'unidades' => '°C',
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
}
