<?php
if (!defined('ABSPATH')) {
  exit;
}

global $wpdb;
$logs = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}nhanhvn_sync_log ORDER BY sync_time DESC LIMIT 50");
?>

<div class="nhanhvn-admin-wrap">
  <h2><?php _e('Sync Logs', 'nhanhvn'); ?></h2>
  <div class="tab-content">
    <form method="post" id="nhanhvn-log-filter" class="filter-form">
      <select name="nhanhvn_log_type" id="nhanhvn-log-type">
        <option value=""><?php _e('All Types', 'nhanhvn'); ?></option>
        <option value="order_sync"><?php _e('Order Sync', 'nhanhvn'); ?></option>
        <option value="product_sync"><?php _e('Product Sync', 'nhanhvn'); ?></option>
        <option value="inventory_sync"><?php _e('Inventory Sync', 'nhanhvn'); ?></option>
        <option value="webhook"><?php _e('Webhook', 'nhanhvn'); ?></option>
        <option value="settings"><?php _e('Settings', 'nhanhvn'); ?></option>
      </select>
      <select name="nhanhvn_log_status" id="nhanhvn-log-status">
        <option value=""><?php _e('All Statuses', 'nhanhvn'); ?></option>
        <option value="success"><?php _e('Success', 'nhanhvn'); ?></option>
        <option value="error"><?php _e('Error', 'nhanhvn'); ?></option>
      </select>
      <input type="date" name="nhanhvn_log_date" id="nhanhvn-log-date" value="">
      <input type="submit" class="button" value="<?php _e('Filter', 'nhanhvn'); ?>">
    </form>

    <table class="wp-list-table widefat fixed striped" id="nhanhvn-log-table">
      <thead>
        <tr>
          <th><?php _e('Type', 'nhanhvn'); ?></th>
          <th><?php _e('Item ID', 'nhanhvn'); ?></th>
          <th><?php _e('Status', 'nhanhvn'); ?></th>
          <th><?php _e('Message', 'nhanhvn'); ?></th>
          <th><?php _e('Time', 'nhanhvn'); ?></th>
        </tr>
      </thead>
      <tbody>
        <?php if ($logs): ?>
          <?php foreach ($logs as $log): ?>
            <tr>
              <td><?php echo esc_html($log->type); ?></td>
              <td><?php echo esc_html($log->item_id); ?></td>
              <td>
                <span class="<?php echo $log->status === 'success' ? 'sync-status' : 'sync-error'; ?>">
                  <?php echo esc_html($log->status); ?>
                </span>
              </td>
              <td><?php echo esc_html($log->message); ?></td>
              <td><?php echo esc_html($log->sync_time); ?></td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="5"><?php _e('No logs found.', 'nhanhvn'); ?></td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
