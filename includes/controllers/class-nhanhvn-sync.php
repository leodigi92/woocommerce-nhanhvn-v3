<?php
if (!defined('ABSPATH')) {
  exit;
}

class Nhanhvn_Sync
{
  private $api;

  public function __construct($api)
  {
    $this->api = $api;
  }

  public function sync_order($order_id, $old_status, $new_status)
  {
    $order = wc_get_order($order_id);
    if (!$order) {
      Nhanhvn_Sync_Log::log('order_sync', $order_id, 'error', 'Order not found');
      return;
    }

    $data = [
      'id' => $order->get_id(),
      'status' => nhanhvn_map_order_status($new_status),
      'total' => $order->get_total(),
      'items' => [],
    ];

    foreach ($order->get_items() as $item) {
      $product = $item->get_product();
      $data['items'][] = [
        'product_id' => $product ? $product->get_id() : 0,
        'quantity' => $item->get_quantity(),
        'price' => $item->get_subtotal(),
      ];
    }

    $response = $this->api->update_order_status($order_id, $data['status']);
    if (isset($response['error'])) {
      Nhanhvn_Sync_Log::log('order_sync', $order_id, 'error', 'Failed to sync order: ' . $response['error']);
    } else {
      Nhanhvn_Sync_Log::log('order_sync', $order_id, 'success', 'Order synced with status ' . $data['status']);
    }
  }

  public function sync_product($product_id, $product)
  {
    $data = [
      'id' => $product_id,
      'name' => $product->get_name(),
      'price' => $product->get_price(),
      'stock' => $product->get_stock_quantity(),
    ];

    $response = $this->api->sync_product($data);
    if (isset($response['error'])) {
      Nhanhvn_Sync_Log::log('product_sync', $product_id, 'error', 'Failed to sync product: ' . $response['error']);
    } else {
      Nhanhvn_Sync_Log::log('product_sync', $product_id, 'success', 'Product synced');
    }
  }

  public function sync_inventory($product_id, $quantity)
  {
    $response = $this->api->update_inventory($product_id, $quantity);
    if (isset($response['error'])) {
      Nhanhvn_Sync_Log::log('inventory_sync', $product_id, 'error', 'Failed to sync inventory: ' . $response['error']);
    } else {
      Nhanhvn_Sync_Log::log('inventory_sync', $product_id, 'success', 'Inventory synced to ' . $quantity);
    }
  }

  public function manual_sync_products()
  {
    $products = wc_get_products(['limit' => -1]);
    foreach ($products as $product) {
      $this->sync_product($product->get_id(), $product);
    }
  }

  public function manual_sync_orders()
  {
    $args = [
      'limit' => -1,
      'type' => 'shop_order',
    ];
    $orders = wc_get_orders($args);
    foreach ($orders as $order) {
      $this->sync_order($order->get_id(), '', $order->get_status());
    }
  }

  public function manual_sync_inventory()
  {
    $products = wc_get_products(['limit' => -1]);
    foreach ($products as $product) {
      $this->sync_inventory($product->get_id(), $product->get_stock_quantity());
    }
  }
}
