<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Menu;
use Illuminate\Console\Command;

class InsertCategory extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'insert_category';

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
        $data = $this->data;
        foreach ($data as $i => $item) {
            $item['sort'] = $i + 1;
            Category::newCategory($item);
            foreach ($item['menu'] as $i_ => $item_menu) {
                $item_menu['category_id'] = $i + 1;
                Menu::newMenu($item_menu);
            }
        }


    }

    protected $data = [
        [
            'name' => 'Trang chủ',
            'description' => 'Trang chủ',
            'notes' => null,
            'status' => 1,
            'data_link' => 'home',
            'icon' => 'iconsminds-shop-4',
            'menu' => [
                [
                    'name' => 'Trang chủ',
                    'description' => 'Trang chủ',
                    'path' => '/',
                    'icon' => 'simple-icon-rocket',
                ],
                [
                    'name' => 'Nạp tiền',
                    'description' => 'Nạp tiền',
                    'path' => '/nap-tien',
                    'icon' => 'iconsminds-coins',
                ],
                [
                    'name' => 'Nhật ký hoạt động',
                    'description' => 'Nhật ký hoạt động',
                    'path' => '/lich-su',
                    'icon' => 'simple-icon-event',
                ],
            ]
        ],
        [
            'name' => 'Quản trị hệ thống',
            'description' => 'Trang chủ',
            'notes' => 'admin',
            'status' => 1,
            'data_link' => '_admin',
            'icon' => 'iconsminds-shop-4',
            'menu' => [
                [
                    'name' => 'Quản lý khách hàng',
                    'description' => 'Quản lý khách hàng',
                    'path' => '/admin_/users',
                    'icon' => 'simple-icon-rocket',
                ],
                [
                    'name' => 'Cài đặt giá',
                    'description' => 'Cài đặt giá',
                    'path' => '/admin_/prices',
                    'icon' => 'simple-icon-rocket',
                ],
                [
                    'name' => 'Cài đặt trang',
                    'description' => 'Cài đặt trang',
                    'path' => '/admin_/config',
                    'icon' => 'simple-icon-rocket',
                ],
            ]
        ],
        [
            'name' => 'DV Facebook chất lượng cao',
            'description' => 'DV Facebook chất lượng cao',
            'notes' => null,
            'status' => 1,
            'data_link' => 'dv_fb',
            'icon' => 'iconsminds-facebook',
            'menu' => [
                [
                    'name' => 'Tăng like bài viết',
                    'description' => 'Tăng like bài viết',
                    'path' => 'facebook/tang-like',
                    'icon' => 'simple-icon-like',
                ],
                [
                    'name' => 'Tăng like bình luận',
                    'description' => 'Tăng like bình luận',
                    'path' => 'facebook/tang-like-binh-luan',
                    'icon' => 'simple-icon-like',
                ],
                [
                    'name' => 'Tăng bình luận',
                    'description' => 'Tăng bình luận',
                    'path' => 'facebook/tang-binh-luan',
                    'icon' => 'd-inline-block',
                ],
                [
                    'name' => 'Tăng follow',
                    'description' => 'Tăng follow',
                    'path' => 'facebook/tang-theo-doi',
                    'icon' => 'd-inline-block',
                ],
                [
                    'name' => 'Tăng like page',
                    'description' => 'Tăng like page',
                    'path' => 'facebook/tang-like-page',
                    'icon' => 'simple-icon-action-undo',
                ],
                [
                    'name' => 'Tăng share bài viết',
                    'description' => 'Tăng share bài viết',
                    'path' => 'facebook/tang-share',
                    'icon' => 'simple-icon-like',
                ],
                [
                    'name' => 'Tăng mem group',
                    'description' => 'Tăng mem group',
                    'path' => 'facebook/tang-mem-group',
                    'icon' => 'iconsminds-mens',
                ],
                [
                    'name' => 'Tăng view video',
                    'description' => 'Tăng view video',
                    'path' => 'facebook/tang-view',
                    'icon' => 'simple-icon-graph',
                ],
            ]
        ],
        [
            'name' => 'Vip Like Chất lượng',
            'description' => 'Vip Like Chất lượng',
            'notes' => null,
            'status' => 1,
            'data_link' => 'vip_facebook',
            'icon' => 'iconsminds-facebook',
            'menu' => [
                [
                    'name' => 'Vip like clone giá rẻ',
                    'description' => 'Vip like clone giá rẻ',
                    'path' => 'vip-facebook/vip-clone',
                    'icon' => 'd-inline-block',
                ],
                [
                    'name' => 'Vip like tháng',
                    'description' => 'Vip like tháng',
                    'path' => 'vip-facebook/vip-like',
                    'icon' => 'd-inline-block',
                ],
                [
                    'name' => 'Vip like số lượng',
                    'description' => 'Vip like số lượng',
                    'path' => 'vip-facebook/vip-sl',
                    'icon' => 'd-inline-block',
                ],
                [
                    'name' => 'Vip cảm xúc',
                    'description' => 'Vip cảm xúc',
                    'path' => 'vip-facebook/vip-cam-xuc',
                    'icon' => 'd-inline-block',
                ],
                [
                    'name' => 'Vip bình luận',
                    'description' => 'Vip bình luận',
                    'path' => 'vip-facebook/vip-comment',
                    'icon' => 'd-inline-block',
                ],
            ]
        ],
        [
            'name' => 'Vip Like giá rẻ',
            'description' => 'Vip Like giá rẻ',
            'notes' => null,
            'status' => 1,
            'data_link' => 'vip_facebook_sale',
            'icon' => 'iconsminds-facebook',
            'menu' => [
                [
                    'name' => 'Vip like clone giá rẻ',
                    'description' => 'Vip like clone giá rẻ',
                    'path' => 'vip-facebook-sale/vip-clone',
                    'icon' => 'd-inline-block',
                ]
            ]
        ],
        [
            'name' => 'Tiktok',
            'description' => 'Tiktok',
            'notes' => null,
            'status' => 0,
            'data_link' => 'tiktok',
            'icon' => 'iconsminds-facebook',
            'menu' => [
                [
                    'name' => 'Vip like clone giá rẻ',
                    'description' => 'Vip like clone giá rẻ',
                    'path' => 'vip-facebook-sale/vip-clone',
                    'icon' => 'd-inline-block',
                    'status' => 0
                ]
            ]
        ],
        [
            'name' => 'Instagram',
            'description' => 'Instagram',
            'notes' => null,
            'status' => 0,
            'data_link' => 'instagram',
            'icon' => 'iconsminds-facebook',
            'menu' => [
                [
                    'name' => 'Vip like clone giá rẻ',
                    'description' => 'Vip like clone giá rẻ',
                    'path' => 'vip-facebook-sale/vip-clone',
                    'icon' => 'd-inline-block',
                    'status' => 0
                ]
            ]
        ],
    ];
}
