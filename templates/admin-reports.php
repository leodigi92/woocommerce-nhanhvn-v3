<?php
if (!defined('ABSPATH')) {
  exit;
}

global $wpdb;
$stats = $wpdb->get_row("
    SELECT
        SUM(CASE WHEN type = 'product_sync' AND status = 'success' THEN 1 ELSE 0 END) as product_success,
        SUM(CASE WHEN type = 'product_sync' AND status = 'error' THEN 1 ELSE 0 END) as product_failed,
        SUM(CASE WHEN type = 'order_sync' AND status = 'success' THEN 1 ELSE 0 END) as order_success,
        SUM(CASE WHEN type = 'order_sync' AND status = 'error' THEN 1 ELSE 0 END) as order_failed,
        SUM(CASE WHEN type = 'customer_sync' AND status = 'success' THEN 1 ELSE 0 END) as customer_success,
        SUM(CASE WHEN type = 'customer_sync' AND status = 'error' THEN 1 ELSE 0 END) as customer_failed
    FROM {$wpdb->prefix}nhanhvn_sync_log
");
?>

<div class="nhanhvn-reports">
  <h2><?php _e('Sync Reports', 'nhanhvn'); ?></h2>
  <table class="widefat">
    <thead>
      <tr>
        <th><?php _e('Type', 'nhanhvn'); ?></th>
        <th><?php _e('Successful', 'nhanhvn'); ?></th>
        <th><?php _e('Failed', 'nhanhvn'); ?></th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td><?php _e('Products', 'nhanhvn'); ?></td>
        <td><?php echo intval($stats->product_success); ?></td>
        <td><?php echo intval($stats->product_failed); ?></td>
      </tr>
      <tr>
        <td><?php _e('Orders', 'nhanhvn'); ?></td>
        <td><?php echo intval($stats->order_success); ?></td>
        <td><?php echo intval($stats->order_failed); ?></td>
      </tr>
      <tr>
        <td><?php _e('Customers', 'nhanhvn'); ?></td>
        <td><?php echo intval($stats->customer_success); ?></td>
        <td><?php echo intval($stats->customer_failed); ?></td>
      </tr>
    </tbody>
  </table>
</div>
