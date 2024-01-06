<?php
$category = getCategory();
$collectCategory = collect($category);
function checkActive($path)
{
    $uri = $_SERVER['REQUEST_URI'];
    if ($uri == '/' && $path == '/') {
        return 'mm-active';
    } else {
        if ($path != '/') {
            $pattern = str_replace("/", "\/", $path);
            if (preg_match('/' . $pattern . '/i', $uri)) {
                return 'mm-active';
            }
            return '';
        }
    }
}
?>
<div class="sidebar-wrapper" data-simplebar="true">
    <div class="sidebar-header">
        <div>
            <img src="/assets/images/logo-cut.jpg" class="logo-icon" alt="logo icon"/>
        </div>
        <div>
            <h4 class="logo-text">Baostar.pro</h4>
        </div>
        <div class="toggle-icon ms-auto"><i class="bx bx-arrow-to-left"></i></div>
    </div>
    <!--navigation-->
    <ul class="metismenu" id="menu">
        @foreach($category as $item)
            <?php
            ?>
            <li class="menu-label">{{$item->name}}</li>
            @foreach($item->category as $item_category)
                {{--mm-active--}}
                <li>
                    <a href="javascript:;" class="has-arrow">
                        <div class="parent-icon"><i class="{{$item_category->icon ?? 'bx bx-home-circle'}}"></i></div>
                        <div class="menu-title">{{$item_category->name}}</div>
                    </a>
                    <ul>
                        @foreach($item_category->menu as $item_menu)
                            <li class="{{checkActive($item_menu->path)}}">
                                <a href="{{$item_menu->path}}"><i
                                        class="{{$item_category->icon ?? 'bx bx-right-arrow-alt'}}"></i>{{$item_menu->name}}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </li>
            @endforeach
        @endforeach

    </ul>
    <!--end navigation-->
</div>

<script src="/assets/js/app.js"></script>
