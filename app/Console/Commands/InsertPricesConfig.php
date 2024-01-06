<?php

namespace App\Console\Commands;

use App\Models\Prices;
use App\Models\PricesConfig;
use Illuminate\Console\Command;

class InsertPricesConfig extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'insert_prices_config';

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
        foreach ($this->level as $item_level) {
            $prices = Prices::all();
            foreach ($prices as $itemPrice) {
                if (!PricesConfig::getPricesLevel($item_level['value'], $itemPrice->id)) {
                    PricesConfig::newPrices([
                        'price_id' => $itemPrice->id,
                        'menu_id' => $itemPrice->menu_id,
                        'category_id' => $itemPrice->category_id,
                        'level_id' => $item_level['value'],
                        'price' => $itemPrice->prices * 100,
                        'status' => 1,
                        'min' => $itemPrice->min,
                        'sort' => $itemPrice->sort,
                        'max' => $itemPrice->max,
                        'package_name' => $itemPrice->package_name,
                        'package_name_mfb' => $itemPrice->package_name_mfb,
                        'name' => $itemPrice->name,
                    ]);
                }
            }
        }
    }

    protected $level = [
        [
            'name' => 'Khách hàng',
            'value' => 1,
        ], [
            'name' => 'Đại lý',
            'value' => 2,
        ], [
            'name' => 'Nhà phân phối',
            'value' => 3,
        ], [
            'name' => 'Nhà Phân phối cấp 1',
            'value' => 4,
        ],
    ];
}
