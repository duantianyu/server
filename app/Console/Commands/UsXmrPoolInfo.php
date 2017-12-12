<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;



class UsXmrPoolInfo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'usxmrpool';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get information from usxmrpool';
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
        $wallet_address = env('XMR_WALLET_ADDRESS');
        $time = time() . '000';
        $url = 'https://www.usxmrpool.com:8119/stats_address?' . 'address=' . $wallet_address . '&longpoll=false&_=' .$time;

        $res = file_get_contents($url);
        Storage::append($this->log_name, date('Y-m-d H:i:s') . ' ' . $res);

        $msg = '';
        json_decode($res, true);
        if(json_last_error() == JSON_ERROR_NONE){
            $res = json_decode($res, true);
            if(isset($res['stats']) && isset($res['stats']['hashrate']) && isset($res['stats']['balance']) && isset($res['stats']['hashes'])){
                $time_data = explode(' ', date('Y m d H i'));
                $res_data = [
                    str_replace(' H', '', $res['stats']['hashrate']),
                    $res['stats']['balance'],
                    $res['stats']['hashes'],
                    $res['stats']['lastShare'],
                    json_encode($res['payments']),
                ];

                if(DB::insert('insert into x_usxmrpool (year, month, day, hour, minute, hashrate, balance, hashes, last_share, payments) values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', array_merge($time_data, $res_data))){
                    $msg = 'Succeed';
                }else{
                    $msg = 'Database error';
                }

            }else{
                $msg = 'The information is not complete';
            }

        }else{
            $msg = 'Json error';
        }
        Storage::append($this->log_name, date('Y-m-d H:i:s') . ' ' . $msg);

    }
}
