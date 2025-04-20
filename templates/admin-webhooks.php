<?php
if (!defined('ABSPATH')) {
  exit;
}
?>

<div class="nhanhvn-settings-box">
  <h2><?php _e('Webhook Settings', 'nhanhvn'); ?></h2>
  <form method="post" class="nhanhvn-settings-form">
    <?php wp_nonce_field('nhanhvn_webhook'); ?>
    <table class="form-table">
      <tr>
        <th><label for="nhanhvn_webhook_url"><?php _e('Webhook Callback URL', 'nhanhvn'); ?></label></th>
        <td>
          <input type="text" name="nhanhvn_webhook_url" id="nhanhvn_webhook_url"
            value="<?php echo esc_attr(get_option('nhanhvn_webhook_url')); ?>" class="regular-text">
          <p class="description">
            <?php _e('Enter the HTTPS URL where Nhanh.vn will send webhook events (e.g., order updates, product changes). Default REST API endpoint: ', 'nhanhvn'); ?><code><?php echo esc_url(rest_url('nhanhvn/v1/webhook')); ?></code>
          </p>
          <?php if (empty(get_option('nhanhvn_webhook_url'))): ?>
            <p class="description" style="color: red;">
              <?php _e('Webhook Callback URL is not set. It has been automatically set to the default REST API endpoint.', 'nhanhvn'); ?>
            </p>
          <?php endif; ?>
        </td>
      </tr>
      <tr>
        <th><label for="nhanhvn_webhook_verify_token"><?php _e('Webhook Verify Token', 'nhanhvn'); ?></label></th>
        <td>
          <input type="text" name="nhanhvn_webhook_verify_token" id="nhanhvn_webhook_verify_token"
            value="<?php echo esc_attr(get_option('nhanhvn_webhook_verify_token')); ?>" class="regular-text" readonly>
          <p class="description">
            <?php _e('This token is used to verify webhook requests from Nhanh.vn. It is auto-generated when registering a webhook.', 'nhanhvn'); ?>
          </p>
        </td>
      </tr>
    </table>
    <p class="submit">
      <input type="submit" name="nhanhvn_register_webhook" class="button nhanhvn-register-webhook"
        value="<?php _e('Register Webhook', 'nhanhvn'); ?>">
      <input type="submit" name="nhanhvn_unregister_webhook" class="button nhanhvn-unregister-webhook"
        value="<?php _e('Unregister Webhook', 'nhanhvn'); ?>">
    </p>
  </form>
</div>
