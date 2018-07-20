<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class HuoBiUsdtSell extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'huobiusdtsell {id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get huobiUsdt sell price';
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

        $id = $this->argument('id');
        $url = 'https://otc-api.huobi.com/v1/data/trade/list/public?country=37&currency=1&payMethod=0&currPage=1&coinId=2&tradeType=0&merchant=1&online=1';//sell
        $res = $this->getByCurl($url);

        Storage::append($this->log_name, date('Y-m-d H:i:s') . '||' . $id . '||' . $res);


        $msg = '';
        json_decode($res, true);
        if (json_last_error() == JSON_ERROR_NONE) {
            $res = json_decode($res, true);
            if (isset($res['code']) && $res['code'] == 200) {
                if (count($res['data']) > 2) {
                    $res_data = [
                        $res['data'][0]['price'],
                        $res['data'][1]['price'],
                        $res['data'][2]['price'],
                    ];
                } elseif (count($res['data']) > 1) {
                    $res_data = [
                        $res['data'][0]['price'],
                        $res['data'][1]['price'],
                        0,
                    ];
                } elseif (count($res['data']) > 0) {
                    $res_data = [
                        $res['data'][0]['price'],
                        0,
                        0,
                    ];
                } else {
                    $res_data = [
                        0,
                        0,
                        0,
                    ];
                }
                $res_data = array_combine(['sell_price', 'sell_price1', 'sell_price2'], $res_data);


                if (DB::table('x_huobi_usdt')
                    ->where('id', $id)
                    ->update($res_data)) {
                    $msg = 'Succeed';

                } else {
                    $msg = 'Database error';
                }

            } else {
                $msg = 'The information is not complete';
            }

        } else {
            $msg = 'Json error';
        }
        Storage::append($this->log_name, date('Y-m-d H:i:s') . '||' . $msg);

    }


    public function getByCurl($url)
    {
        $ch = curl_init();
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, 0);
        curl_setopt($ch, CURLOPT_PROXY, '127.0.0.1:1088');
        //curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
        curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5_HOSTNAME);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/67.0.3396.87 Safari/537.36 OPR/54.0.2952.54');

        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
    }


}