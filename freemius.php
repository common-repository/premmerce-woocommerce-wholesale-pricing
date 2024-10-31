<?php

// Create a helper function for easy SDK access.
function premmerce_ppt_fs() {
    global $premmerce_ppt_fs;

    if ( ! isset( $premmerce_ppt_fs ) ) {
        // Include Freemius SDK.
        require_once dirname(__FILE__) . '/freemius/start.php';

        $premmerce_ppt_fs = fs_dynamic_init( array(
            'id'                  => '1585',
            'slug'                => 'premmerce-woocommerce-wholesale-pricing',
            'type'                => 'plugin',
            'public_key'          => 'pk_360267a12d63880b41b7a3f4be283',
            'is_premium'          => false,
            'has_addons'          => false,
            'has_paid_plans'      => false,
            'menu'                => array(
                'account'        => false,
                'contact'        => false,
                'support'        => false,
            ),
        ) );
    }

    return $premmerce_ppt_fs;
}

// Init Freemius.
premmerce_ppt_fs();
// Signal that SDK was initiated.
do_action( 'premmerce_ppt_fs_loaded' );
