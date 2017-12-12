<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;



class SupportXmr extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'supportxmr';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get information from supportxmr';
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
        $url = 'https://supportxmr.com/api/miner/' . $wallet_address . '/stats';

        $res = file_get_contents($url);
        Storage::append($this->log_name, date('Y-m-d H:i:s') . ' ' . $res);

        $msg = '';
        json_decode($res, true);
        if(json_last_error() == JSON_ERROR_NONE){
            $res = json_decode($res, true);
            if(isset($res['hash']) && isset($res['identifier']) && isset($res['lastHash']) && isset($res['totalHashes'])){
                $time_data = explode(' ', date('Y m d H i'));
                $res_data = [
                    $res['hash'],
                    $res['identifier'],
                    $res['lastHash'],
                    $res['totalHashes'],
                    $res['validShares'],
                    $res['invalidShares'],
                    $res['amtPaid'],
                    $res['amtDue'],
                    $res['txnCount'],
                ];

                if(DB::insert('insert into x_supportxmr (year, month, day, hour, minute, hash, identifier, last_hash, total_hashes, valid_shares, invalid_shares, amt_paid, amt_due, txn_count) values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', array_merge($time_data, $res_data))){
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
