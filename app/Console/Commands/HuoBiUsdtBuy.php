<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Helpers;

class HuoBiUsdtBuy extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'huobiusdtbuy';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get huobiUsdt buy price';
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

        $url = 'https://otc-api.huobi.com/v1/data/trade/list/public?country=37&currency=1&payMethod=0&currPage=1&coinId=2&tradeType=1&merchant=1&online=1';//buy
        $res = Helpers::getByCurl($url);

        Storage::append($this->log_name, date('Y-m-d H:i:s') . '||' . $res);


        $msg = '';
        json_decode($res, true);
        if (json_last_error() == JSON_ERROR_NONE) {
            $res = json_decode($res, true);
            if (isset($res['code']) && $res['code'] == 200) {
                $time_data = explode(' ', date('Y m d H i'));
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


                if ($id = DB::table('x_huobi_usdt')->insertGetId(array_combine(['year', 'month', 'day', 'hour', 'minute', 'buy_price', 'buy_price1', 'buy_price2'], array_merge($time_data, $res_data)))) {
                    $msg = $id . '||Succeed';

                    $this->call('huobiusdtsell', [
                        'id' => $id,
                    ]);
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





}