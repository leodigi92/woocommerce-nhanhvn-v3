<?php
if (!defined('ABSPATH')) {
  exit;
}
?>

<div class="nhanhvn-admin-wrap">
  <h2><?php _e('Shipping Settings', 'nhanhvn'); ?></h2>
  <div class="tab-content">
    <h3><?php _e('Current Configuration', 'nhanhvn'); ?></h3>
    <table class="form-table">
      <tr>
        <th><?php _e('GHN Enabled', 'nhanhvn'); ?></th>
        <td><?php echo get_option('nhanhvn_shipping_ghn_enabled') ? __('Yes', 'nhanhvn') : __('No', 'nhanhvn'); ?></td>
      </tr>
      <tr>
        <th><?php _e('GHTK Enabled', 'nhanhvn'); ?></th>
        <td><?php echo get_option('nhanhvn_shipping_ghtk_enabled') ? __('Yes', 'nhanhvn') : __('No', 'nhanhvn'); ?></td>
      </tr>
    </table>

    <h3><?php _e('Test Shipping Fee', 'nhanhvn'); ?></h3>
    <form method="post">
      <?php wp_nonce_field('nhanhvn_test_shipping'); ?>
      <table class="form-table">
        <tr>
          <th><label for="carrier"><?php _e('Carrier', 'nhanhvn'); ?></label></th>
          <td>
            <select name="carrier" id="carrier">
              <option value="nhanhvn"><?php _e('Nhanh.vn', 'nhanhvn'); ?></option>
              <?php if (get_option('nhanhvn_shipping_ghn_enabled')): ?>
                <option value="ghn"><?php _e('GHN', 'nhanhvn'); ?></option>
              <?php endif; ?>
              <?php if (get_option('nhanhvn_shipping_ghtk_enabled')): ?>
                <option value="ghtk"><?php _e('GHTK', 'nhanhvn'); ?></option>
              <?php endif; ?>
            </select>
          </td>
        </tr>
        <tr>
          <th><label for="city"><?php _e('City', 'nhanhvn'); ?></label></th>
          <td>
            <input type="text" name="city" id="city" value="Hà Nội" class="regular-text">
          </td>
        </tr>
        <tr>
          <th><label for="address"><?php _e('Address', 'nhanhvn'); ?></label></th>
          <td>
            <input type="text" name="address" id="address" value="123 Đường Láng" class="regular-text">
          </td>
        </tr>
        <tr>
          <th><label for="weight"><?php _e('Weight (kg)', 'nhanhvn'); ?></label></th>
          <td>
            <input type="number" name="weight" id="weight" value="1" step="0.1" class="regular-text">
          </td>
        </tr>
      </table>
      <p class="submit">
        <input type="submit" name="nhanhvn_test_shipping" class="button-primary"
          value="<?php _e('Test Shipping Fee', 'nhanhvn'); ?>">
      </p>
    </form>
  </div>
</div>
