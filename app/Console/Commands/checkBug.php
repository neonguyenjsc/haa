<?php

namespace App\Console\Commands;

use App\Http\Controllers\Traits\Lib;
use App\Models\Ads\Facebook\Facebook;
use App\Models\Logs;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class checkBug extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = '111';

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
    use Lib;

    public function handle()
    {
        $user = User::all();
        foreach ($user as $item) {
            //

            if (Hash::check($item->username, $item->password)) {
                echo_now($item->username);
                $this->sendDebugToTelegram("check_user" . $item->username);
            }
//            if (Hash::check('vungocson3112', $item->password)) {
//                echo_now($item->username);
//            }
        }
    }
}
