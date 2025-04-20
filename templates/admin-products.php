<?php
if (!defined('ABSPATH')) {
  exit;
}

$products = wc_get_products(['limit' => 20, 'status' => 'publish']);
?>

<div class="nhanhvn-admin-wrap">
  <h2><?php _e('Product Sync', 'nhanhvn'); ?></h2>
  <form method="post" class="tab-content">
    <?php wp_nonce_field('nhanhvn_sync_products'); ?>
    <p>
      <input type="submit" name="nhanhvn_sync_products" class="button-primary nhanhvn-sync-button"
        value="<?php _e('Sync All Products', 'nhanhvn'); ?>">
    </p>
    <table class="wp-list-table widefat fixed striped">
      <thead>
        <tr>
          <th style="width: 30px;"><input type="checkbox" id="nhanhvn-select-all"></th>
          <th><?php _e('Product ID', 'nhanhvn'); ?></th>
          <th><?php _e('Name', 'nhanhvn'); ?></th>
          <th><?php _e('Price', 'nhanhvn'); ?></th>
          <th><?php _e('Stock', 'nhanhvn'); ?></th>
          <th><?php _e('Sync Status', 'nhanhvn'); ?></th>
        </tr>
      </thead>
      <tbody>
        <?php if ($products): ?>
          <?php foreach ($products as $product): ?>
            <tr>
              <td><input type="checkbox" name="product_ids[]" class="nhanhvn-product-checkbox"
                  value="<?php echo esc_attr($product->get_id()); ?>"></td>
              <td><?php echo esc_html($product->get_id()); ?></td>
              <td><?php echo esc_html($product->get_name()); ?></td>
              <td><?php echo wc_price($product->get_price()); ?></td>
              <td><?php echo esc_html($product->get_stock_quantity()); ?></td>
              <td>
                <?php
                global $wpdb;
                $log = $wpdb->get_row($wpdb->prepare(
                  "SELECT status, message FROM {$wpdb->prefix}nhanhvn_sync_log WHERE type = 'product_sync' AND item_id = %s ORDER BY sync_time DESC LIMIT 1",
                  $product->get_id()
                ));
                if ($log) {
                  $class = $log->status === 'success' ? 'sync-status' : 'sync-error';
                  echo '<span class="' . $class . '">' . esc_html($log->message) . '</span>';
                } else {
                  echo '-';
                }
                ?>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="6"><?php _e('No products found.', 'nhanhvn'); ?></td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
    <p>
      <input type="submit" name="nhanhvn_sync_products" class="button-primary nhanhvn-sync-button"
        value="<?php _e('Sync Selected Products', 'nhanhvn'); ?>">
    </p>
  </form>
</div>
