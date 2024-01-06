<?php

namespace App\Console\Commands;

use App\Models\Level;
use App\Models\Prices;
use App\Models\PricesConfig;
use Illuminate\Console\Command;

class UpdatePricesConfig extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update_prices_config';

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
        $level = Level::all();
        $prices = Prices::all();
        foreach ($level as $item_level) {
            foreach ($prices as $item_prices) {
                $p_cf = PricesConfig::where('price_id', $item_prices->id)->where('level_id', $item_level->id)->first();
                if ($p_cf) {
                    $p_cf->name = $item_prices->name;
                    $p_cf->description = $item_prices->description;
                    $p_cf->status = $item_prices->status;
                    $p_cf->package_name = $item_prices->package_name;
                    $p_cf->sort = $item_prices->sort;
                    $p_cf->active = $item_prices->active;
                    $p_cf->save();
                } else {
                    PricesConfig::newPricesConfig([
                        'menu_id' => $item_prices->menu_id,
                        'prices' => $item_prices->prices * 10,
                        'name' => $item_prices->name,
                        'status' => $item_prices->status,
                        'level_id' => $item_level->id,
                        'package_name' => $item_prices->package_name,
                        'sort' => $item_prices->sort,
                        'description' => $item_prices->description,
                        'active' => $item_prices->active,
                        'price_id' => $item_prices->id,
                    ]);
                }
            }
        }
    }
}
