<?php

use Premmerce\PriceTypes\PriceTypesPlugin;

/**
 *
 * @wordpress-plugin
 * Plugin Name:       Premmerce Wholesale Pricing for WooCommerce
 * Plugin URI:        https://premmerce.com
 * Description:       Premmerce Wholesale Pricing for WooCommerce is a plugin that allows you to add individual wholesale prices or other price types for WooCommerce products to  any customers roles.
 * Version:           1.1.10
 * Author:            Premmerce
 * Author URI:        https://profiles.wordpress.org/premmerce
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       premmerce-price-types
 * Domain Path:       /languages
 *
 * WC requires at least: 3.0.0
 * WC tested up to: 7.3.0
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

call_user_func( function () {

	require_once plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';

    if(!get_option('premmerce_version')){
        require_once plugin_dir_path(__FILE__) . '/freemius.php';
    }

	$main = new PriceTypesPlugin(__FILE__);

	register_activation_hook( __FILE__, [ $main, 'activate' ] );

	register_uninstall_hook( __FILE__, [ PriceTypesPlugin::class, 'uninstall' ] );

	$main->run();
} );
