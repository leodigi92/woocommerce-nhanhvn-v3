<?php if (!defined('ABSPATH'))
  exit; ?>
<div class="nhanhvn-settings">
  <h2><?php _e('General Settings', 'nhanhvn'); ?></h2>
  <form method="post" action="">
    <?php wp_nonce_field('nhanhvn_settings'); ?>
    <table class="form-table">
      <tr>
        <th scope="row"><label for="nhanhvn_app_id"><?php _e('App ID', 'nhanhvn'); ?></label></th>
        <td>
          <input type="text" name="nhanhvn_app_id" id="nhanhvn_app_id"
            value="<?php echo esc_attr(get_option('nhanhvn_app_id')); ?>" class="regular-text" />
          <p class="description"><?php _e('Enter your Nhanh.vn App ID.', 'nhanhvn'); ?></p>
        </td>
      </tr>
      <tr>
        <th scope="row"><label for="nhanhvn_secret_key"><?php _e('Secret Key', 'nhanhvn'); ?></label></th>
        <td>
          <input type="text" name="nhanhvn_secret_key" id="nhanhvn_secret_key"
            value="<?php echo esc_attr(get_option('nhanhvn_secret_key')); ?>" class="regular-text" />
          <p class="description"><?php _e('Enter your Nhanh.vn Secret Key.', 'nhanhvn'); ?></p>
        </td>
      </tr>
      <tr>
        <th scope="row"><label for="nhanhvn_redirect_url"><?php _e('Redirect URL', 'nhanhvn'); ?></label></th>
        <td>
          <input type="text" name="nhanhvn_redirect_url" id="nhanhvn_redirect_url"
            value="<?php echo esc_attr(get_option('nhanhvn_redirect_url_custom', admin_url('admin.php'))); ?>"
            class="regular-text" />
          <p class="description"><?php _e('Redirect URL configured in Nhanh.vn app.', 'nhanhvn'); ?></p>
        </td>
      </tr>
      <tr>
        <th scope="row"><label><?php _e('OAuth URL', 'nhanhvn'); ?></label></th>
        <td>
          <a href="<?php echo esc_url((new Nhanhvn_Admin())->get_oauth_url()); ?>"
            class="button"><?php _e('Get OAuth URL', 'nhanhvn'); ?></a>
          <p class="description"><?php _e('Click to get the OAuth URL for Nhanh.vn authorization.', 'nhanhvn'); ?></p>
        </td>
      </tr>
      <tr>
        <th scope="row"><label for="nhanhvn_access_code"><?php _e('Access Code', 'nhanhvn'); ?></label></th>
        <td>
          <input type="text" name="nhanhvn_access_code" id="nhanhvn_access_code" value="" class="regular-text" />
          <p class="description">
            <?php _e('Enter the Access Code returned by Nhanh.vn after authorization.', 'nhanhvn'); ?>
          </p>
        </td>
      </tr>
      <tr>
        <th scope="row"><label for="nhanhvn_access_token"><?php _e('Access Token', 'nhanhvn'); ?></label></th>
        <td>
          <textarea name="nhanhvn_access_token" id="nhanhvn_access_token" class="large-text" rows="3"
            readonly><?php echo esc_textarea(get_option('nhanhvn_access_token')); ?></textarea>
          <p class="description"><?php _e('Access Token retrieved from Nhanh.vn.', 'nhanhvn'); ?></p>
        </td>
      </tr>
      <tr>
        <th scope="row"><label><?php _e('Shipping Integration', 'nhanhvn'); ?></label></th>
        <td>
          <label>
            <input type="checkbox" name="nhanhvn_shipping_ghn_enabled" value="1" <?php checked(get_option('nhanhvn_shipping_ghn_enabled'), 1); ?> />
            <?php _e('Enable GHN Shipping', 'nhanhvn'); ?>
          </label>
          <br />
          <label>
            <input type="checkbox" name="nhanhvn_shipping_ghtk_enabled" value="1" <?php checked(get_option('nhanhvn_shipping_ghtk_enabled'), 1); ?> />
            <?php _e('Enable GHTK Shipping', 'nhanhvn'); ?>
          </label>
        </td>
      </tr>
    </table>
    <p class="submit">
      <input type="submit" name="nhanhvn_save_settings" class="button-primary"
        value="<?php _e('Save Settings', 'nhanhvn'); ?>" />
      <?php wp_nonce_field('nhanhvn_get_access_token'); ?>
      <input type="submit" name="nhanhvn_get_access_token" class="button"
        value="<?php _e('Get Access Token', 'nhanhvn'); ?>" />
    </p>
  </form>
</div>
