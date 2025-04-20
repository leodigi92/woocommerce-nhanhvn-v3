<?php
if (!defined('ABSPATH')) {
  exit;
}

function nhanhvn_map_order_status($status)
{
  $status_map = [
    'pending' => 'pending',
    'processing' => 'processing',
    'on-hold' => 'on-hold',
    'completed' => 'completed',
    'cancelled' => 'cancelled',
    'refunded' => 'refunded',
    'failed' => 'failed',
  ];

  return isset($status_map[$status]) ? $status_map[$status] : 'pending';
}

function nhanhvn_calculate_package_weight($package)
{
  $weight = 0;
  foreach ($package['contents'] as $item) {
    $product = $item['data'];
    $weight += floatval($product->get_weight()) * $item['quantity'];
  }
  return $weight;
}
