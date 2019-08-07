<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Helpers;

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
        //$url = 'https://otc-api.huobi.com/v1/data/trade/list/public?country=37&currency=1&payMethod=0&currPage=1&coinId=2&tradeType=0&merchant=1&online=1';//sell
        $url = 'https://otc-api.eiijo.cn/v1/data/trade-market?coinId=1&currency=1&tradeType=sell&currPage=1&payMethod=0&country=37&blockType=general&online=1&range=0&amount=';//sell
        $res = Helpers::getByCurl($url, true);

        //Storage::append($this->log_name, date('Y-m-d H:i:s') . '||' . $id . '||' . $res);


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
        //Storage::append($this->log_name, date('Y-m-d H:i:s') . '||' . $msg);

    }


}