<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Helpers;

class HuoBiCoin extends Command
{
    protected $table = 'x_huobi_coin';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'huobicoin';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get the btc/eth/eos/ltc/ht price from huobi';
    protected $log_name = __CLASS__ . '.log';


    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $arr_symbol = ['btc', 'eth', 'eos', 'ltc', 'ht'];
        $base_url = 'https://api.huobi.pro/market/detail?symbol=';
        array_walk($arr_symbol, function (&$v) use ($base_url) {
            $v = $base_url . $v . 'usdt';
        });
        $curl_res = Helpers::getByCurlMulti($arr_symbol, true);
        //Storage::append($this->log_name, date('Y-m-d H:i:s') . '||' . json_encode($curl_res));

        $id = 0;
        foreach ($curl_res as $res) {
            $msg = '';
            json_decode($res, true);
            if (json_last_error() == JSON_ERROR_NONE) {
                $res = json_decode($res, true);
                if (isset($res['status']) && $res['status'] == 'ok') {
                    $data = [];
                    if ($id == 0) {
                        $time_data = explode(' ', date('Y m d H i'));
                        $data = array_combine(['year', 'month', 'day', 'hour', 'minute'], $time_data);
                    }

                    $data['ts'] = substr($res['ts'], 0, strlen($res['ts']) - 3);
                    $arr_ch = explode('.', $res['ch'], -1);
                    $symbol = trim(str_replace('usdt', '', end($arr_ch)));
                    $data[$symbol] = $res['tick']['close'];

                    if ($id == 0) {
                        $id = DB::table($this->table)->insertGetId($data);
                        $msg .= 'id:' . $id . '||' . $symbol . ' Succeed||';

                    } else {
                        DB::table($this->table)->where('id', $id)->update($data);
                        $msg .= 'update ' . $symbol . ' Succeed||';

                    }

                } else {
                    $msg .= 'The information is not complete||';
                }

            } else {
                $msg .= 'Json error||';
            }
            //Storage::append($this->log_name, date('Y-m-d H:i:s') . '||' . rtrim($msg, '||'));

        }

    }


}