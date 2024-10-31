<?php

if ( ! defined('WPINC')) {
    die;
}

if (function_exists('premmerce_ppt_fs') && premmerce_ppt_fs()->is_registered()) {
    premmerce_ppt_fs()->add_filter('hide_account_tabs', '__return_true');
    premmerce_ppt_fs()->_account_page_load();
    premmerce_ppt_fs()->_account_page_render();
}
