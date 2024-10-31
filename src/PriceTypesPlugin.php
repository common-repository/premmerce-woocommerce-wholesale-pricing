<?php namespace Premmerce\PriceTypes;

use Premmerce\PriceTypes\Admin\Admin;
use Premmerce\PriceTypes\Admin\AdminOrders;
use Premmerce\PriceTypes\Admin\AdminProducts;
use Premmerce\PriceTypes\Frontend\Frontend;
use Premmerce\PriceTypes\Models\Model;
use Premmerce\SDK\V2\FileManager\FileManager;
use Premmerce\SDK\V2\Notifications\AdminNotifier;

/**
 * Class PriceTypesPlugin
 *
 * @package Premmerce\PriceTypes
 */
class PriceTypesPlugin
{
    const DOMAIN = 'premmerce-price-types';

    /**
     * @var string
     */
    public static $version = '';

    /**
     * @var string
     */
    private $mainFile;

    /**
     * @var FileManager
     */
    private $fileManager;

    /**
     * @var Model
     */
    private $model;

    /**
     * @var AdminNotifier
     */
    private $notifier;

    /**
     * PluginManager constructor.
     *
     * @param $mainFile
     */
    public function __construct($mainFile)
    {
        $this->mainFile = $mainFile;
        
        $this->fileManager = new FileManager($this->mainFile);
        $this->notifier = new AdminNotifier();
        $this->model = new Model();

        self::$version = $this->getPluginVersionFromMainFileDocBlock();

        add_action('init', array( $this, 'loadTextDomain' ));
        add_action('admin_init', array( $this, 'checkRequirePlugins' ));
    }

    /**
     * Run plugin part
     */
    public function run()
    {
        $valid = count($this->validateRequiredPlugins()) === 0;

        if (is_admin()) {
            $adminOrders = new AdminOrders($this->model, $this->fileManager);
            new Admin($this->fileManager, $this->model, $adminOrders);

            if ($valid) {
                new AdminProducts($this->fileManager, $this->model);

                if (wp_doing_ajax()) {
                    new Frontend($this->model);
                }
            }
        } elseif ($valid) {
            new Frontend($this->model);
        }
    }

    /**
     * Fired when the plugin is activated
     */
    public function activate()
    {
        $this->model->createTables();
    }

    /**
     * Fired during plugin uninstall
     */
    public static function uninstall()
    {
        $model = new Model();
        $model->deleteTables();
    }

    /**
     * Check required plugins and push notifications
     */
    public function checkRequirePlugins()
    {
        $message = __('The %s plugin requires %s plugin to be active!', self::DOMAIN);

        $plugins = $this->validateRequiredPlugins();

        if (count($plugins)) {
            foreach ($plugins as $plugin) {
                $error = sprintf($message, 'Premmerce Wholesale Pricing for WooCommerce', $plugin);
                $this->notifier->push($error, AdminNotifier::ERROR, false);
            }
        }
    }

    /**
     * Validate required plugins
     *
     * @return array
     */
    private function validateRequiredPlugins()
    {
        $plugins = array();

        if (!function_exists('is_plugin_active')) {
            include_once(ABSPATH . 'wp-admin/includes/plugin.php');
        }

        /**
         * Check if WooCommerce is active
         **/
        if (!(is_plugin_active('woocommerce/woocommerce.php') || is_plugin_active_for_network('woocommerce/woocommerce.php'))) {
            $plugins[] = '<a target="_blank" href="https://wordpress.org/plugins/woocommerce/">WooCommerce</a>';
        }

        return $plugins;
    }

    /**
     * Load plugin translations
     */
    public function loadTextDomain()
    {
        $name = $this->fileManager->getPluginName();
        load_plugin_textdomain('premmerce-price-types', false, $name . '/languages/');
    }

    /**
     * @return string
     */
    private function getPluginVersionFromMainFileDocBlock()
    {
        if (!function_exists('get_plugin_data')) {
            include_once(ABSPATH . 'wp-admin/includes/plugin.php');
        }

        $pluginData = get_plugin_data($this->mainFile, false, false);
        return isset($pluginData['Version']) ? $pluginData['Version'] : '';
    }
}
