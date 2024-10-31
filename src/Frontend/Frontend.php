<?php namespace Premmerce\PriceTypes\Frontend;

use Premmerce\PriceTypes\Models\Model;
use WC_Product;

/**
 * Class Frontend
 *
 * @package Premmerce\PriceTypes\Frontend
 */
class Frontend
{
    /**
     * @var Model
     */
    private $model;

    /**
     * Frontend constructor.
     *
     * @param Model $model
     */
    public function __construct(Model $model)
    {
        $this->model = $model;

        // set prices for products
        add_filter('woocommerce_product_get_price', array($this,'getProductPrice'), 101, 2);
        add_filter('woocommerce_product_get_sale_price', array($this,'getProductPrice'), 101, 2);
        add_filter('woocommerce_product_get_regular_price', array($this,'getProductPrice'), 101, 2);

        // set prices for variation
        add_filter('woocommerce_product_variation_get_price', array($this,'getProductPrice'), 101, 2);
        add_filter('woocommerce_product_variation_get_sale_price', array($this,'getProductPrice'), 101, 2);
        add_filter('woocommerce_product_variation_get_regular_price', array($this,'getProductPrice'), 101, 2);

        // set variate range html
        add_filter('woocommerce_variation_prices_price', array($this,'getProductPrice'), 101, 2);
        add_filter('woocommerce_variation_prices_sale_price', array($this,'getProductPrice'), 101, 2);
        add_filter('woocommerce_variation_prices_regular_price', array($this,'getProductPrice'), 101, 2);

        add_filter('woocommerce_get_variation_prices_hash', array($this, 'updateWoocommercePricesHash'), 10, 3);
    }

    /**
     * Replace product price
     *
     * @param string $price
     * @param WC_Product $product
     *
     * @return string
     */
    public function getProductPrice($price, $product)
    {
        if (apply_filters('premmerce_wholesale_only_for_registered', is_user_logged_in())) {
            $user = wp_get_current_user();

            $price = (float) wc_format_decimal($price);
            $lowestPriceByTypes = $this->model->getLowestAvailablePriceForUserByProduct($user, $product, $price);
            $price = min($price, $lowestPriceByTypes);
        }

        $price =  $price ?: '';

        return (string) $price;
    }

    /**
     * Extend Woocommerce prices hash so different users can have own prices cache
     *
     * @param array $hash
     * @param WC_Product $product
     * @param bool $forDisplay
     *
     * @return array
     */
    public function updateWoocommercePricesHash($hash, WC_Product $product, $forDisplay)
    {
        $userCurrency = '';
        if (function_exists('premmerce_multicurrency')) {
            $multicurrencyApi = premmerce_multicurrency();
            $userCurrency = $multicurrencyApi->getUsersCurrencyId();
        }

        $currentUserId = (string) get_current_user_id();
        $hash[] = wc_tax_enabled() . $forDisplay . $userCurrency . $currentUserId;
        return $hash;
    }
}
