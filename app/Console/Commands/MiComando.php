<?php

namespace App\Console\Commands;

use App\Models\Sensor;
use Illuminate\Console\Command;
use GuzzleHttp\Client;
use App\Models\Registro;
use Schema;

class MiComando extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'consumo:api';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'consume y guarda la informacion de la api';

    private $key;
    private $username;
    private $api;
    private $client;

    public function __construct()
    {
        parent::__construct();
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

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        while (true) {
            $this->info('Consumiendo API');

            $ubicacion = $this->client->get($this->username . '/feeds/ubicacion');
            $ubi_valor = $this->convertirInfo($ubicacion);

            $id_ubi = $this->client->get($this->username . '/feeds/idubicacion');
            $idubi_valor = $this->convertirInfo($id_ubi);

            $idkey = Sensor::where('sku', $idubi_valor)->value('id');

            $this->guardarInfo($ubi_valor, 'LtLg', $idkey);


            $distancia = $this->client->get($this->username . '/feeds/distancia');
            $dist_valor = $this->convertirInfo($distancia);

            $id_dis = $this->client->get($this->username . '/feeds/iddistancia');
            $iddis_valor = $this->convertirInfo($id_dis);

            $idkeydist = Sensor::where('sku', $iddis_valor)->value('id');

            $this->guardarInfo($dist_valor, 'cm', $idkeydist);
            
            sleep(25);
            //return 0;
        }
    }
    public function convertirInfo($response) {
        $json_response = json_decode($response->getBody()->getContents(), true);
            
        $valor = $json_response['last_value'];
        return $valor;
    }

    public function guardarInfo($valor, $unidades, $sensor_id) {
        $registro = Registro::create([
            'valor' => $valor,
            'unidades' => $unidades,
            'sensor_id' => $sensor_id
        ]);

        if (!$registro) {
            $this->error('Error al guardar registro!');
        }

        $this->info('Registro guardado correctamente');
    }
}
