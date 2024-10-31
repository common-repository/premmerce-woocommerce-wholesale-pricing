<?php

namespace Premmerce\PriceTypes\Admin;

use Premmerce\PriceTypes\Models\Model;
use Premmerce\SDK\V2\FileManager\FileManager;
use \WC_Order;
use \WC_Order_Item;

class AdminOrders
{
    /**
     * @var Model
     */
    private $model;

    /**
     * @var FileManager
     */
    private $fileManager;

    /**
     * AdminOrders constructor.
     *
     * @param Model $model
     * @param FileManager $fileManager
     */
    public function __construct(Model $model, FileManager $fileManager)
    {
        $this->model = $model;
        $this->fileManager = $fileManager;
    }

    /**
     * Loop through order items and update prices
     *
     * @param WC_Order $order
     */
    public function updateOrderProductsPrices(WC_Order $order)
    {
        $customerId = $order->get_customer_id();

        foreach ($order->get_items() as $item) {
            $this->setLowestPossiblePriceToWcOrderItem($item, $customerId);
        }
    }

    /**
     * Update single order item price
     *
     * @param WC_Order_Item $item
     * @param int $customerId
     */
    public function setLowestPossiblePriceToWcOrderItem(WC_Order_Item $item, $customerId)
    {
        $user = get_user_by('id', $customerId);

        if ($user) {
            $product = $this->model->getProductFromOrderItem($item);
            $productPrice = (float) wc_format_decimal($product->get_price());
            $userPrice = $this->model->getLowestAvailablePriceForUserByOrderItem($user, $item, $productPrice);
            $item->set_total($userPrice);
        }
    }

    /**
     * Set item price for current customer if possible
     *
     * @param $itemId
     * @param WC_Order_Item $item
     * @param WC_Order $order
     */
    public function setAdminOrderItemPrice($itemId, WC_Order_Item $item, WC_Order $order)
    {
        if ($order->get_customer_id()) {
            $this->setLowestPossiblePriceToWcOrderItem($item, $order->get_customer_id());
        }
    }
}
