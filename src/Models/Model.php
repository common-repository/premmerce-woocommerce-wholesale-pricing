<?php namespace Premmerce\PriceTypes\Models;

use \WC_Product;
use \WP_User;
use \WC_Order_Item;

/**
 * Class Model
 * @package Premmerce\PriceTypes\Models
 */
class Model
{
    /**
     * Db table name without prefix
     */
    const TBL_NAME_PRICE_TYPES = 'premmerce_price_types';

    /**
     * Db table name without prefix
     */
    const TBL_NAME_PRICE_TYPES_ROLES = 'premmerce_price_types_roles';

    /**
     * Prefix for DB table
     *
     * @var string
     */
    private $prefix = '';

    /**
     * Charset for DB table
     *
     * @var string
     */
    private $charset = '';

    /**
     * Collate for DB table
     *
     * @var string
     */
    private $collate = '';

    /**
     * Model constructor.
     *
     */
    public function __construct()
    {
        global $wpdb;

        $this->prefix  = $wpdb->prefix;
        $this->charset = $wpdb->charset;
        $this->collate = $wpdb->collate;
    }

    /**
     * Create plugin tables
     */
    public function createTables()
    {
        $sql = vsprintf(
            'CREATE TABLE IF NOT EXISTS %s (
              `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
              `name` varchar(255) NOT NULL DEFAULT "",
              PRIMARY KEY  (`ID`)
            ) ENGINE=InnoDB DEFAULT CHARACTER SET %s COLLATE %s;',
            array(
                $this->prefix . self::TBL_NAME_PRICE_TYPES,
                $this->charset,
                $this->collate
            )
        );

        $this->runQuery($sql);

        $sql = vsprintf(
            'CREATE TABLE IF NOT EXISTS %s (
                `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                `price_type_id` bigint(20) unsigned DEFAULT NULL,
                `role` varchar(255) NOT NULL DEFAULT "",
                PRIMARY KEY  (`ID`)
            ) ENGINE=InnoDB DEFAULT CHARACTER SET %s COLLATE %s;',
            array(
                $this->prefix . self::TBL_NAME_PRICE_TYPES_ROLES,
                $this->charset,
                $this->collate
            )
        );

        $this->runQuery($sql);
    }

    /**
     * Delete plugin tables.
     */
    public function deleteTables()
    {
        $sql = 'DROP TABLE IF EXISTS ' . $this->prefix . self::TBL_NAME_PRICE_TYPES;
        $this->runQuery($sql);

        $sql = 'DROP TABLE IF EXISTS ' . $this->prefix . self::TBL_NAME_PRICE_TYPES_ROLES;
        $this->runQuery($sql);
    }

    /**
     * Create new price type
     *
     * @param array $priceTypes
     *
     * @return int
     */
    public function addPriceTypes($priceTypes)
    {
        $sql = vsprintf(
            'INSERT INTO `%s` (`name`) VALUES (\'%s\');',
            array(
                $this->getTblNamePriceTypes(),
                $priceTypes['name'],
            )
        );

        if ($this->runQuery($sql)) {
            $lastId = $this->getInsertId();

            if ($this->addPriceTypesRoles($lastId, $priceTypes['roles'])) {
                return $lastId;
            }
        }
    }

    /**
     * Create new price types roles
     *
     * @param int $priceTypeId
     * @param array $roles
     *
     * @return bool
     */
    private function addPriceTypesRoles($priceTypeId, $roles)
    {
        $values = array();
        foreach ($roles as $role) {
            $values[] = vsprintf('(%u, \'%s\')', array(
                $priceTypeId,
                $role,
            ));
        }

        $sql = vsprintf(
            'INSERT INTO `%s` (`price_type_id`,`role`) VALUES %s;',
            array(
                $this->getTblNamePriceTypesRoles(),
                implode(', ', $values),
            )
        );

        return $this->runQuery($sql);
    }

    /**
     * Edit price type
     *
     * @param array $priceTypes
     *
     * @return bool
     */
    public function editPriceTypes($priceTypes)
    {
        $sql = vsprintf(
            'UPDATE `%s` SET `name` = \'%s\' WHERE `ID` = %s;',
            array(
                $this->getTblNamePriceTypes(),
                $priceTypes['name'],
                $priceTypes['ID'],
            )
        );

        if ($this->runQuery($sql)) {
            return $this->editPriceTypesRoles($priceTypes);
        }
    }

    /**
     * Edit price types roles
     *
     * @param $priceTypes
     *
     * @return false|int
     */
    private function editPriceTypesRoles($priceTypes)
    {
        if ($this->deletePriceTypesRoles(array($priceTypes['ID']))) {
            return $this->addPriceTypesRoles($priceTypes['ID'], $priceTypes['roles']);
        }
    }

    /**
     * Delete price types by ids list
     *
     * @param array $ids
     *
     * @return bool
     */
    public function deletePriceTypes($ids = array())
    {
        $sql = vsprintf(
            'DELETE FROM `%s` WHERE `id` IN (%s);',
            array(
                $this->getTblNamePriceTypes(),
                implode(', ', $ids)
            )
        );

        if ($this->runQuery($sql)) {
            $this->deletePriceTypesRoles($ids);
            $this->deletePriceTypesPrices($ids);

            return true;
        }
    }

    /**
     * Delete price types roles by ids list
     *
     * @param array $priceTypeIds
     *
     * @return bool
     */
    private function deletePriceTypesRoles($priceTypeIds = array())
    {
        $sql = vsprintf(
            'DELETE FROM `%s` WHERE `price_type_id` IN (%s);',
            array(
                $this->getTblNamePriceTypesRoles(),
                implode(', ', $priceTypeIds)
            )
        );

        return $this->runQuery($sql);
    }

    /**
     * Delete price types prices by ids list
     *
     * @param array $priceTypeIds
     *
     * @return bool
     */
    private function deletePriceTypesPrices($priceTypeIds = array())
    {
        $values = array();
        foreach ($priceTypeIds as $id) {
            $values[] = '\'_price_types_' . $id . '\'';
        }

        $sql = vsprintf(
            'DELETE FROM `wp_postmeta` WHERE `meta_key` IN (%s);',
            array(
                implode(', ', $values)
            )
        );

        return $this->runQuery($sql);
    }

    /**
     * Get table name with DB prefix
     *
     * @return string
     */
    private function getTblNamePriceTypes()
    {
        return $this->prefix . self::TBL_NAME_PRICE_TYPES;
    }

    /**
     * Get table name with DB prefix
     *
     * @return string
     */
    private function getTblNamePriceTypesRoles()
    {
        return $this->prefix . self::TBL_NAME_PRICE_TYPES_ROLES;
    }

    /**
     * Get id of last inserted row
     *
     * @return int
     */
    private function getInsertId()
    {
        global $wpdb;

        return $wpdb->insert_id;
    }

    /**
     * Get data from tables
     *
     * @param string $sql
     *
     * @return array
     */
    private function getResults($sql)
    {
        global $wpdb;


        $key = md5($sql);

        $result = wp_cache_get($key);

        if (false !== $result) {
            return $result;
        }

        $results = $wpdb->get_results($sql, ARRAY_A);

        wp_cache_set($key, $results);

        return $results;
    }

    /**
     * Get price type
     *
     * @param string $id
     *
     * @return array
     */
    public function getPriceType($id)
    {
        $data = array();

        $sql = vsprintf(
            'SELECT `t1`.`ID` AS "ID", `t2`.`ID` AS "roleID", `t1`.`name`, `t2`.`role`
            FROM `%s` AS t1
            LEFT JOIN `%s` AS t2 ON t2.price_type_id = t1.id
            WHERE `t1`.`ID` = %s;',
            array(
                $this->getTblNamePriceTypes(),
                $this->getTblNamePriceTypesRoles(),
                $id
            )
        );

        $priceTypes = $this->getResults($sql);

        if ($priceTypes) {
            foreach ($priceTypes as $key => $priceType) {
                $data['ID']                          = $priceType['ID'];
                $data['name']                        = $priceType['name'];
                $data['roles'][$priceType['roleID']] = $priceType['role'];
            }
        }

        return $data;
    }

    /**
     * Get data from db table price_types
     *
     * @return array
     */
    public function getPriceTypes()
    {
        $data = array();

        $sql = vsprintf(
            'SELECT `t1`.`ID` AS "ID", `t2`.`ID` AS "roleID", `t1`.`name`, `t2`.`role`
            FROM `%s` AS t1
            LEFT JOIN `%s` AS t2 ON t2.price_type_id = t1.id;',
            array(
                $this->getTblNamePriceTypes(),
                $this->getTblNamePriceTypesRoles(),
            )
        );

        $priceTypes = $this->getResults($sql);

        if ($priceTypes) {
            foreach ($priceTypes as $key => $priceType) {
                $data[$priceType['ID']]['ID']                          = $priceType['ID'];
                $data[$priceType['ID']]['name']                        = $priceType['name'];
                $data[$priceType['ID']]['roles'][$priceType['roleID']] = $priceType['role'];
            }
        }

        return $data;
    }

    /**
     * Get data from db table price_types_roels
     *
     * @param int $priceTypeId
     *
     * @return array
     */
    public function getPriceTypesRoles($priceTypeId = 0)
    {
        $data = array();

        $where = '';
        if ($priceTypeId) {
            $where = vsprintf(
                'WHERE `price_type_id` <> %s',
                array(
                    $priceTypeId,
                )
            );
        }

        $sql = vsprintf(
            'SELECT * FROM `%s` %s;',
            array(
                $this->getTblNamePriceTypesRoles(),
                $where
            )
        );

        $roles = $this->getResults($sql);

        if ($roles) {
            foreach ($roles as $role) {
                $data[] = $role['role'];
            }
        }

        return $data;
    }

    /**
     * Get list of price types ids by user roles
     *
     * @param array $roles
     *
     * @return array
     */
    public function getPriceTypesByUserRoles($roles)
    {
        $roles = apply_filters('premmerce_wholesale_get_price_type_by_user_roles', $roles);
        $roles = array_map(function ($item) {
            return '\'' . $item . '\'';
        }, $roles);

        $sql = vsprintf(
            'SELECT `price_type_id` FROM `%s` WHERE `role` IN (%s);',
            array(
                $this->getTblNamePriceTypesRoles(),
                implode(', ', $roles),
            )
        );

        $data = $this->getResults($sql);

        $priceTypes = $data ? array_column($data, 'price_type_id') : array();

        return $priceTypes;
    }

    /**
     * Execute query
     *
     * @param string $sql
     *
     * @return bool
     */
    private function runQuery($sql)
    {
        global $wpdb;

        if ($wpdb->query($sql) !== false) {
            return true;
        }
    }

    /**
     * Check price type id is it exist
     *
     * @param int $id
     *
     * @return bool
     */
    public function validatePriceTypeId($id)
    {
        $sql = vsprintf(
            'SELECT * FROM `%s` WHERE `id` = %s',
            array(
                $this->getTblNamePriceTypes(),
                $id
            )
        );

        return (bool) $this->getResults($sql);
    }

    /**
     * Get lowest price for product from available for user price types
     *
     * @param array $priceTypesIds
     * @param WC_Product $product
     * @param float $commonPrice
     *
     * @return float
     */
    public function getLowestAvailablePrice(array $priceTypesIds, WC_Product $product, $commonPrice = null)
    {
        $newPrices = $this->getProductPricesByPriceTypes($priceTypesIds, $product);

        if (null !== $commonPrice) {
            $newPrices[] = $commonPrice;
        }

        $price = apply_filters('premmerce_wholesale_pricing_get_price', min($newPrices), $product);

        return (float) wc_format_decimal($price);
    }

    /**
     * Get lowest price for order item
     *
     * @param WP_User $user
     * @param WC_Order_Item $item
     * @param null $currentPrice
     *
     * @return float
     */
    public function getLowestAvailablePriceForUserByOrderItem(WP_User $user, WC_Order_Item $item, $currentPrice = null)
    {
        $product = $this->getProductFromOrderItem($item);
        $productPrice = $this->getLowestAvailablePriceForUserByProduct($user, $product, $currentPrice);
        return $productPrice * $item->get_quantity();
    }

    /**
     * Get lowest from all product available prices for user
     *
     * @param WP_User $user
     * @param WC_Product $product
     * @param $currentPrice
     * @return float
     */
    public function getLowestAvailablePriceForUserByProduct(WP_User $user, WC_Product $product, $currentPrice)
    {
        $userPriceTypes = $this->getPriceTypesByUserRoles($user->roles);

        return $this->getLowestAvailablePrice($userPriceTypes, $product, $currentPrice);
    }

    /**
     * Check if AJAX request was sent from admin post page
     *
     * @return bool
     */
    public function isAjaxFromAdmin()
    {
        $result = false;
        if (wp_doing_ajax()) {
            $referrer = wp_get_raw_referer();
            $urlPath = parse_url($referrer, PHP_URL_PATH);
            $adminPath = parse_url(admin_url(), PHP_URL_PATH);

            $result = (bool) stristr($urlPath, $adminPath);
        }

        return $result;
    }

    /**
     * @param WC_Order_Item $item
     *
     * @return false|WC_Product|null
     */
    public function getProductFromOrderItem(WC_Order_Item $item)
    {
        $itemProductId = $item->get_variation_id() ?: $item->get_product_id();
        return wc_get_product($itemProductId);
    }

    /**
     * Get all prices for product by price types ids
     *
     * @param array $priceTypesIds
     * @param WC_Product $product
     *
     * @return array
     */
    private function getProductPricesByPriceTypes(array $priceTypesIds, WC_Product $product)
    {
        $prices = array();
        foreach ($priceTypesIds as $priceType) {
            $value = get_post_meta($product->get_ID(), '_price_types_' . $priceType, true);

            if ($value) {
                $prices[] = (float) wc_format_decimal($value);
            }
        }

        return $prices;
    }
}
