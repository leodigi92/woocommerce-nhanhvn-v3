<?php
if (!defined('ABSPATH')) {
  exit;
}
?>

<div class="nhanhvn-help">
  <h2><?php _e('Help & Documentation', 'nhanhvn'); ?></h2>
  <p>
    <?php _e('Welcome to Nhanh.vn Integration plugin. This plugin helps you sync products, orders, customers, inventory, and shipping with Nhanh.vn.', 'nhanhvn'); ?>
  </p>

  <h3><?php _e('Getting Started', 'nhanhvn'); ?></h3>
  <ol>
    <li><?php _e('Obtain your Nhanh.vn API key from your Nhanh.vn account.', 'nhanhvn'); ?></li>
    <li><?php _e('Go to WooCommerce > Nhanh.vn Sync > Settings and enter your API key.', 'nhanhvn'); ?></li>
    <li><?php _e('Enable desired shipping methods (GHN, GHTK) if needed.', 'nhanhvn'); ?></li>
    <li><?php _e('Use the Manual Sync section to sync all products or orders.', 'nhanhvn'); ?></li>
    <li><?php _e('Check the Sync Status or Reports tab to monitor sync progress.', 'nhanhvn'); ?></li>
  </ol>

  <h3><?php _e('Troubleshooting', 'nhanhvn'); ?></h3>
  <ul>
    <li><?php _e('If sync fails, check the Sync Status tab for error messages.', 'nhanhvn'); ?></li>
    <li><?php _e('Ensure your API key is correct and has the necessary permissions.', 'nhanhvn'); ?></li>
    <li><?php _e('Verify that your server allows outgoing HTTP requests to Nhanh.vn API.', 'nhanhvn'); ?></li>
    <li><?php _e('Check WooCommerce logs (WooCommerce > Status > Logs) for detailed errors.', 'nhanhvn'); ?></li>
  </ul>

  <h3><?php _e('Support', 'nhanhvn'); ?></h3>
  <p><?php _e('For further assistance, contact the developer at:', 'nhanhvn'); ?> <a
      href="mailto:support@leodigi92.com">support@leodigi92.com</a></p>
</div>
