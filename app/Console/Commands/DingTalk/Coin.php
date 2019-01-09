<?php

namespace App\Console\Commands\DingTalk;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Helpers;

class Coin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dingtalkcoin';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Report Usdt price';
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

        $webhook = env('DINGTALK_ROBOT_COIN');

        $usdt_info = DB::table('x_huobi_usdt')
            ->select('buy_price', 'buy_price1', 'buy_price2', 'sell_price', 'sell_price1', 'sell_price2', 'created_at')
            ->orderBy('id', 'desc')
            ->first();

        $coin_info = DB::table('x_huobi_coin')
            ->select('btc', 'eth', 'eos', 'ltc', 'ht', 'ts')
            ->orderBy('id', 'desc')
            ->first();

        if((time() - $coin_info->ts) > 70){
            $text = '### 数据获取超时' . PHP_EOL;
        }else{
            $text = '### USDT价格(前三)' . PHP_EOL;
            $text .= "##### 买入价：" . $usdt_info->buy_price . '  ' . $usdt_info->buy_price1 . '  ' . $usdt_info->buy_price2 . PHP_EOL;
            $text .= "##### 卖出价：" . $usdt_info->sell_price . '  ' . $usdt_info->sell_price1 . '  ' . $usdt_info->sell_price2 . PHP_EOL;
            $text .= "##### *USDT价格获取时间：" . $usdt_info->created_at . '*' . PHP_EOL;

            $text .= '###  其他币价格' . PHP_EOL;
            $text .= "##### BTC：" . rtrim($coin_info->btc, '0') . PHP_EOL;
            $text .= "##### ETH：" . rtrim($coin_info->eth, '0') . PHP_EOL;
            $text .= "##### EOS：" . rtrim($coin_info->eos, '0') . PHP_EOL;
            $text .= "##### LTC：" . rtrim($coin_info->ltc, '0') . PHP_EOL;
            $text .= "##### HT ：" . rtrim($coin_info->ht, '0') . PHP_EOL;
            $text .= "##### *其他币获取时间：" . date('Y-m-d H:i:s', $coin_info->ts) . '*' . PHP_EOL;
            $text .= "##### *当前时间：" . date('Y-m-d H:i:s') . '*' . PHP_EOL;
        }


        $data = [
            'msgtype' => 'markdown',
            'markdown' => [
                'title' => date('Y-m-d H:i') . ' USDT价格(前三)',
                'text' => $text,
            ],
            'at' => [
                'atMobiles' => [],
                'isAtAll' => true,
            ],
        ];
        $res = Helpers::requestByCurl($webhook, json_encode($data));

        Storage::append($this->log_name, date('Y-m-d H:i:s') . '||' . $res);

    }


}