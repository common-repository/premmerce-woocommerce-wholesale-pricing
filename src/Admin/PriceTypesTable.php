<?php namespace Premmerce\PriceTypes\Admin;

use Premmerce\PriceTypes\Models\Model;
use Premmerce\SDK\V2\FileManager\FileManager;

class PriceTypesTable extends \WP_List_Table
{
    /**
     * @var Model
     */
    private $adminModel;

    /**
     * @var FileManager
     */
    private $fileManager;

    /**
     * PriceTypesTable constructor.
     *
     * @param FileManager $fileManager
     * @param Model $model
     */
    public function __construct(FileManager $fileManager, Model $model)
    {
        $this->current_action();

        parent::__construct(array(
            'singular' => __('types', 'premmerce-price-types'),
            'plural'   => __('type', 'premmerce-price-types'),
            'ajax'     => false,
        ));

        $this->fileManager = $fileManager;
        $this->adminModel = $model;

        $this->_column_headers = array(
            $this->get_columns()
        );

        $this->prepare_items();
    }

    public function no_items()
    {
        _e('No price types found.', 'premmerce-price-types');
    }

    /**
     * Fill checkbox field
     *
     * @param array $item
     *
     * @return string
     */
    protected function column_cb($item)
    {
        return '<input type="checkbox" name="ids[]" id="cb-select-' . $item['ID'] . '" value="' . $item['ID'] . '">';
    }

    /**
     * Fill name field
     *
     * @param array $item
     *
     * @return string
     */
    protected function column_name($item)
    {
        return vsprintf(
            '<a href="%s?page=premmerce-price-types&item=%s">%s</a>',
            array(
                admin_url('admin.php'),
                $item['ID'],
                $item['name']
            )
        );
    }

    /**
     * Fill roles field
     *
     * @param $item
     *
     * @return string
     */
    protected function column_roles($item)
    {
        return implode(', ', $item['roles']);
    }

    /**
     * Return array with columns titles
     *
     * @return array
     */
    public function get_columns()
    {
        return array(
            'cb'   => '<input type="checkbox">',
            'name' => __('Name', 'premmerce-price-types'),
            'roles' => __('Roles', 'premmerce-price-types'),
        );
    }

    /**
     * Set actions list for bulk
     *
     * @return array
     */
    protected function get_bulk_actions()
    {
        return array(
            'delete' => __('Delete', 'premmerce-price-types'),
        );
    }

    /**
     * Set items data in table
     */
    public function prepare_items()
    {
        $this->items = $this->adminModel->getPriceTypes();
    }

    /**
     * Generate row actions
     *
     * @param object $item
     * @param string $column_name
     * @param string $primary
     *
     * @return string
     */
    protected function handle_row_actions($item, $column_name, $primary)
    {
        if ($primary !== $column_name) {
            return '';
        }

        $actions['edit'] = vsprintf('<a href="%s?page=premmerce-price-types&item=%s">%s</a>', array(
            admin_url('admin.php'),
            $item['ID'],
            __('Edit', 'premmerce-price-types'),
        ));

        $actions['delete'] = vsprintf('<a class="submitdelete" href="%s?action=%s&price_type=%s" data-action--delete>%s</a>', array(
            admin_url('admin-post.php'),
            'premmerce_delete_price_type',
            $item['ID'],
            __('Delete', 'premmerce-price-types'),
        ));

        return $this->row_actions($actions);
    }

    protected function display_tablenav($which)
    {
        $this->fileManager->includeTemplate('admin/bulk-actions.php', array(
            'which'  => $which,
            'table'  => $this
        ));
    }
}
