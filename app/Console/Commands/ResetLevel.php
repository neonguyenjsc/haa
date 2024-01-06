<?php

namespace App\Console\Commands;

use App\Models\PaymentMonth;
use App\Models\User;
use Illuminate\Console\Command;

class ResetLevel extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reset_level';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
     * @return int
     */
    public function handle()
    {
        $y = date('Y');
        $m = date('m');
        $amount_agency = 500000;
        $payment_month1 = PaymentMonth::where('year', $y)->where('month', $m)->where('coin', '>=', $amount_agency)->pluck('user_id')->toArray();
        User::whereIn('id', $payment_month1)->update(['level' => 2]);


        $amount_agency = 10000000;
        $payment_month = PaymentMonth::where('year', $y)->where('month', $m)->where('coin', '>=', $amount_agency)->pluck('user_id')->toArray();
        User::whereIn('id', $payment_month)->update(['level' => 3]);


        User::where('level', '>', 1)->whereNotIn('id',$payment_month1)->whereNotIn('id',$payment_month)->update(['level' => 1]);
        echo "Xong";
        exit();
    }
}
