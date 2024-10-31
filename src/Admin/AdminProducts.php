<?php namespace Premmerce\PriceTypes\Admin;

use Premmerce\PriceTypes\Models\Model;
use Premmerce\SDK\V2\FileManager\FileManager;
use WP_Post;

/**
 * Class AdminProducts
 * @package Premmerce\PriceTypes\Admin
 */
class AdminProducts
{
    /**
     * @var FileManager
     */
    private $fileManager;

    /**
     * @var Model
     */
    private $model;

    /**
     * AdminProducts constructor.
     *
     * @param FileManager $fileManager
     * @param Model $model
     */
    public function __construct(FileManager $fileManager, Model $model)
    {
        $this->fileManager = $fileManager;

        $this->model = $model;

        add_action('woocommerce_product_options_general_product_data', array($this, 'productForm'));
        add_action('woocommerce_process_product_meta', array($this, 'productFormSave'), 10, 1);

        add_action('woocommerce_product_after_variable_attributes', array($this, 'productFormVariable'), 10, 3);
        add_action('woocommerce_save_product_variation', array($this, 'productFormVariableSave'), 10, 2);
    }

    /**
     * Displays price types fields for product (simple,external/affiliate)
     */
    public function productForm()
    {
        $priceTypes = $this->model->getPriceTypes();

        echo '<div class="options_group hide_if_variable">';

        foreach ($priceTypes as $type) {
            woocommerce_wp_text_input(array(
                'id' => '_price_types[' . $type['ID'] . ']',
                'label' => $type['name'] . ' (' . get_woocommerce_currency_symbol() . ')',
                'value' => get_post_meta(get_the_ID(), '_price_types_' . $type['ID'], true),
                'data_type' => 'price',
                'custom_attributes' => array('price-type--field' => ''),
            ));
        }

        echo '</div>';
    }

    /**
     * Displays price types fields for product (variable)
     *
     * @param int $i
     * @param array $variationData
     * @param WP_Post $variation
     */
    public function productFormVariable($i, $variationData, WP_Post $variation)
    {
        $priceTypes = $this->model->getPriceTypes();

        echo '<div>';
        
        foreach ($priceTypes as $type) {
            woocommerce_wp_text_input(array(
                'id' => 'variable_price_types[' . $i . '][' . $type['ID'] . ']',
                'label' => $type['name'] . ' (' . get_woocommerce_currency_symbol() . ')',
                'value' => get_post_meta($variation->ID, '_price_types_' . $type['ID'], true),
                'data_type' => 'price',
                'custom_attributes' => array('price-type--field' => ''),
            ));
        }

        echo '</div>';
    }

    /**
     * Save/Update price types fields for product (simple,external/affiliate)
     *
     * @param int $id
     */
    public function productFormSave($id)
    {
        if (isset($_POST['_price_types'])) {
            $data = $_POST['_price_types'];

            foreach ($data as $key => $value) {
                update_post_meta($id, '_price_types_' . $key, $value);
            }
        }
    }

    /**
     * Save/Update price types fields for product (variable)
     *
     * @param int $variationId
     * @param int $i
     */
    public function productFormVariableSave($variationId, $i)
    {
        if (isset($_POST['product_id']) && isset($_POST['variable_price_types'])) {
            $data = $_POST['variable_price_types'][$i];

            foreach ($data as $key => $value) {
                update_post_meta($variationId, '_price_types_' . $key, $value);
            }
        }
    }
}
