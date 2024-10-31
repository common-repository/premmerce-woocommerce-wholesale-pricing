<?php namespace Premmerce\PriceTypes\Admin;

use Premmerce\PriceTypes\Models\Model;
use Premmerce\SDK\V2\FileManager\FileManager;

/**
 * Class Admin
 *
 * @package Premmerce\PriceTypes\Admin
 */
class Admin
{
    /**
     * @var FileManager
     */
    private $fileManager;

    /**
     * @var Model
     */
    private $model;

    const MENU_SLUG = 'premmerce-price-types';

    /**
     * Admin constructor.
     *
     * Register menu items and handlers
     *
     * @param FileManager $fileManager
     * @param Model $model
     * @param AdminOrders $adminOrders
     */
    public function __construct(FileManager $fileManager, Model $model, AdminOrders $adminOrders)
    {
        $this->fileManager = $fileManager;

        $this->model = $model;

        add_action('admin_menu', array($this, 'addMenuPage'));
        add_action('admin_menu', array($this, 'addFullPack'), 100);

        add_action('init', function () {
            if (!session_id()) {
                session_start();
            }
        });

        add_action('admin_post_premmerce_create_price_type', array($this, 'createPriceType'));
        add_action('admin_post_premmerce_update_price_type', array($this, 'updatePriceType'));
        add_action('admin_post_premmerce_delete_price_type', array($this, 'deletePriceType'));

        //Admin add/edit order page
        add_action('woocommerce_before_order_object_save', array($adminOrders, 'updateOrderProductsPrices'));
        if ($this->model->isAjaxFromAdmin()) {
            add_action('woocommerce_before_order_item_line_item_html', array($adminOrders, 'setAdminOrderItemPrice'), 10, 3);
        }
    }

    /**
     *  Row action delete price type
     */
    public function deletePriceType()
    {
        if (isset($_GET['price_type']) && !empty($_GET['price_type'])) {
            $this->delete(array('ids' => array($_GET['price_type'])));
        }

        wp_redirect($this->getLinkList());
    }

    /**
     *  Post method for create new price type
     */
    public function createPriceType()
    {
        $this->add($this->prepare($_POST));
    }

    /**
     *  Post method for update price type
     */
    public function updatePriceType()
    {
        $this->edit($this->prepare($_POST));
    }

    /**
     * Add submenu to premmerce menu page
     *
     * @return false|string
     */
    public function addMenuPage()
    {
        global $admin_page_hooks;

        $premmerceMenuExists = isset($admin_page_hooks['premmerce']);

        $svg = '<svg version="1.1" xmlns="http://www.w3.org/2000/svg" xml:space="preserve" width="20" height="16" style="fill:#82878c" viewBox="0 0 20 16"><g id="Rectangle_7"> <path d="M17.8,4l-0.5,1C15.8,7.3,14.4,8,14,8c0,0,0,0,0,0H8h0V4.3C8,4.1,8.1,4,8.3,4H17.8 M4,0H1C0.4,0,0,0.4,0,1c0,0.6,0.4,1,1,1 h1.7C2.9,2,3,2.1,3,2.3V12c0,0.6,0.4,1,1,1c0.6,0,1-0.4,1-1V1C5,0.4,4.6,0,4,0L4,0z M18,2H7.3C6.6,2,6,2.6,6,3.3V12 c0,0.6,0.4,1,1,1c0.6,0,1-0.4,1-1v-1.7C8,10.1,8.1,10,8.3,10H14c1.1,0,3.2-1.1,5-4l0.7-1.4C20,4,20,3.2,19.5,2.6 C19.1,2.2,18.6,2,18,2L18,2z M14,11h-4c-0.6,0-1,0.4-1,1c0,0.6,0.4,1,1,1h4c0.6,0,1-0.4,1-1C15,11.4,14.6,11,14,11L14,11z M14,14 c-0.6,0-1,0.4-1,1c0,0.6,0.4,1,1,1c0.6,0,1-0.4,1-1C15,14.4,14.6,14,14,14L14,14z M4,14c-0.6,0-1,0.4-1,1c0,0.6,0.4,1,1,1 c0.6,0,1-0.4,1-1C5,14.4,4.6,14,4,14L4,14z"/></g></svg>';
        $svg = 'data:image/svg+xml;base64,' . base64_encode($svg);

        if (!$premmerceMenuExists) {
            add_menu_page(
                'Premmerce',
                'Premmerce',
                'manage_options',
                'premmerce',
                '',
                $svg
            );
        }

        $page = add_submenu_page(
            'premmerce',
            __('Prices', 'premmerce-price-types'),
            __('Prices', 'premmerce-price-types'),
            'manage_options',
            Admin::MENU_SLUG,
            array($this, 'controller')
        );

        if (!$premmerceMenuExists) {
            global $submenu;
            unset($submenu['premmerce'][0]);
        }

        return $page;
    }

    public function addFullPack()
    {
        global $submenu;

        if (!function_exists('get_plugins')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        $plugins = get_plugins();

        $premmerceInstalled = array_key_exists('premmerce-premium/premmerce.php', $plugins)
                              || array_key_exists('premmerce/premmerce.php', $plugins);

        if (!$premmerceInstalled) {
            $submenu['premmerce'][999] = array(
                'Get premmerce full pack',
                'manage_options',
                admin_url('plugin-install.php?tab=plugin-information&plugin=premmerce'),
            );
        }
    }

    /**
     * Control all actions in module
     */
    public function controller()
    {
        $this->registerPluginPageAssets();

        $this->controllerMessages();

        $this->controllerBulkActions();

        if (isset($_GET['item'])) {
            $this->controllerEdit($_GET['item']);
        } else {
            $this->controllerPage();
        }
    }

    /**
     * Control Bulk actions
     *
     */
    private function controllerBulkActions()
    {
        $data = $_POST;

        if (isset($data['action']) && $data['action'] != - 1) {
            $action = $data['action'];
        } elseif (isset($data['action2']) && $data['action2'] != - 1) {
            $action = $data['action2'];
        } else {
            $action = '';
        }

        switch($action) {
            case 'delete':
                $this->delete($data);
                break;
        }
    }

    /**
     * Control list actions
     */
    private function controllerPage()
    {
        $current = isset($_GET['tab'])? $_GET['tab'] : null;

        $tabs['list'] = __('Prices', 'premmerce-price-types');

        if (function_exists('premmerce_ppt_fs')) {
            $tabs['contact'] = __('Contact Us', 'premmerce-price-types');
            if (premmerce_ppt_fs()->is_registered()) {
                $tabs['account'] = __('Account', 'premmerce-price-types');
            }
        }

        $this->fileManager->includeTemplate('admin/main.php', array(
            'table'   => new PriceTypesTable($this->fileManager, $this->model),
            'roles'   => $this->getUnusedRoles(),
            'current' => $current? $current : 'list',
            'tabs'    => $tabs,
        ));
    }

    /**
     * Control edit price type
     *
     * @param int $item
     */
    private function controllerEdit($item)
    {
        if (is_numeric($item)) {
            if (!$this->validate($item)) {
                $this->showMessages();
                wp_die();
            }

            $this->fileManager->includeTemplate('admin/edit.php', array(
                'roles'     => $this->getUnusedRoles($item),
                'priceType' => $this->model->getPriceType((int)$item),
                'deleteUrl' => admin_url('admin-post.php') . '?action=premmerce_delete_price_type&price_type=' . $item,
            ));
        } else {
            wp_redirect($this->getLinkList());
        }
    }

    /**
     * Control show error/complete messages
     */
    private function controllerMessages()
    {
        if (isset($_SESSION['msg']) && !empty($_SESSION['msg'])) {
            $this->showMessages();
        }
    }

    /**
     * Create new Price type
     *
     * @param array $priceType
     */
    private function add($priceType)
    {
        if ($this->validate($priceType)) {
            $lastId = $this->model->addPriceTypes($priceType);

            if (!$lastId) {
                $this->setMessages(array(__('Error.', 'premmerce-price-types')), 'error');
            }
        }

        wp_redirect($_SERVER['HTTP_REFERER']);
    }

    /**
     * Edit Price type
     *
     * @param array $priceType
     */
    private function edit($priceType)
    {
        if ($this->validate($priceType)) {
            if ($this->model->editPriceTypes($priceType)) {
                $this->setMessages(
                    array(__('Price type updated.', 'premmerce-price-types')),
                    'updated',
                    array(
                        'url'  => $this->getLinkList(),
                        'text' => '← ' . __('Back to Premmerce Wholesale Pricing for WooCommerce', 'premmerce-price-types'),
                    )
                );
            } else {
                $this->setMessages(array(__('Error.', 'premmerce-price-types')), 'error');
            }
        }

        wp_redirect($_SERVER['HTTP_REFERER']);
    }

    /**
     * Delete Price types data
     *
     * @param array $data
     */
    private function delete($data)
    {
        if (isset($data['ids'])) {
            if ($this->model->deletePriceTypes($data['ids'])) {
                $this->setMessages(array(__('Deleted.', 'premmerce-price-types')));
            } else {
                $this->setMessages(array(__('Error.', 'premmerce-price-types')), 'error');
            }
        }

        wp_redirect($_SERVER['HTTP_REFERER']);
    }

    /**
     * Prepare data to add/edit
     *
     * @param array $data
     *
     * @return array
     */
    private function prepare($data)
    {
        $defaults = array(
            'ID'    => null,
            'name'  => null,
            'roles' => null,
        );

        $defaults = array_replace($defaults, $data);

        return array(
            'ID'    => $defaults['ID'],
            'name'  => (string)trim($defaults['name']),
            'roles' => (array)$defaults['roles'],
        );
    }

    /**
     * Register assets for admin page
     */
    private function registerPluginPageAssets()
    {
        wp_enqueue_style(Admin::MENU_SLUG . '-style', $this->fileManager->locateAsset('admin/css/style.css'));

        wp_enqueue_style('select2', $this->fileManager->locateAsset('admin/css/select2.min.css'));
        wp_enqueue_script('select2', $this->fileManager->locateAsset('admin/js/select2.min.js'));

        wp_enqueue_script('wc-price-types-scripts', $this->fileManager->locateAsset('admin/js/script.js'));
    }

    /**
     * Set error/complete messages
     *
     * @param array $message
     * @param string $type
     * @param array $link
     */
    private function setMessages($message = array(), $type = 'updated', $link = array())
    {
        $_SESSION['msg']     = $message;
        $_SESSION['msgType'] = $type;

        if ($link) {
            $_SESSION['link'] = $link;
        }
    }

    /**
     * Show error/complete messages
     */
    private function showMessages()
    {
        foreach ($_SESSION['msg'] as $m) {
            $link = '';

            if (isset($_SESSION['link'])) {
                $link = '<p><a href="' . $_SESSION['link']['url'] . '">' . $_SESSION['link']['text'] . '</a></p>';
            }

            echo '<div class="' . $_SESSION['msgType'] . '"><p>' . $m . '</p>' . $link . '</div>';
        }

        unset($_SESSION['msg']);
        unset($_SESSION['msgType']);
        unset($_SESSION['link']);
    }

    /**
     * Return link for list price types
     *
     * @return string
     */
    private function getLinkList()
    {
        return admin_url('admin.php') . '?page=premmerce-price-types';
    }

    /**
     * Get unused roles list
     *
     * @param int $priceTypeId
     *
     * @return array
     */
    public function getUnusedRoles($priceTypeId = 0)
    {
        global $wp_roles;

        $rolesData = array();

        $roles = $this->model->getPriceTypesRoles($priceTypeId);

        foreach ($wp_roles->get_names() as $key => $value) {
            if (!in_array($key, $roles)) {
                $rolesData[ $key ] = $value;
            }
        }

        return $rolesData;
    }

    /**
     * Check data
     *
     * @param array $data
     *
     * @return bool
     */
    private function validate($data)
    {
        $messages = array();

        if (is_array($data)) {
            if (empty($data['name'])) {
                $messages[] = __('Name is empty.', 'premmerce-price-types');
            }

            if (!count($data['roles'])) {
                $messages[] = __('No one role selected.', 'premmerce-price-types');
            }
        }

        if (is_numeric($data)) {
            if (!$this->model->validatePriceTypeId($data)) {
                $messages[] = __('You attempted to edit an item that doesn’t exist. Perhaps it was deleted?', 'premmerce-price-types');
            }
        }

        if (count($messages)) {
            $this->setMessages($messages, 'error');

            return false;
        }

        return true;
    }
}
