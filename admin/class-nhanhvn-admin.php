<?php
if (!defined('ABSPATH')) {
  exit;
}

class Nhanhvn_Admin
{
  public function add_admin_menu()
  {
    add_submenu_page(
      'woocommerce',
      __('Nhanh.vn Sync', 'nhanhvn'),
      __('Nhanh.vn Sync', 'nhanhvn'),
      'manage_woocommerce',
      'nhanhvn-sync',
      [$this, 'render_admin_page']
    );
  }

  public function get_oauth_url()
  {
    $app_id = get_option('nhanhvn_app_id');
    if (!$app_id) {
      return '#';
    }
    $state = wp_create_nonce('nhanhvn_oauth');
    $redirect_url = admin_url('admin.php');
    $params = [
      'version' => '2.0',
      'appId' => $app_id,
      'returnLink' => $redirect_url,
      'state' => $state,
    ];
    $oauth_url = 'https://nhanh.vn/oauth?' . http_build_query($params);
    Nhanhvn_Sync_Log::log('oauth', 'request', 'info', 'OAuth URL generated: ' . $oauth_url);
    return $oauth_url;
  }

  public function render_admin_page()
  {
    $tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'general';

    // Xử lý cài đặt chung
    if ($tab === 'general' && isset($_POST['nhanhvn_save_settings']) && check_admin_referer('nhanhvn_settings')) {
      update_option('nhanhvn_app_id', sanitize_text_field($_POST['nhanhvn_app_id']));
      update_option('nhanhvn_secret_key', sanitize_text_field($_POST['nhanhvn_secret_key']));
      update_option('nhanhvn_redirect_url_custom', sanitize_text_field($_POST['nhanhvn_redirect_url']));
      update_option('nhanhvn_shipping_ghn_enabled', isset($_POST['nhanhvn_shipping_ghn_enabled']) ? 1 : 0);
      update_option('nhanhvn_shipping_ghtk_enabled', isset($_POST['nhanhvn_shipping_ghtk_enabled']) ? 1 : 0);
      Nhanhvn_Sync_Log::log('settings', 'general', 'success', 'Settings updated');
      echo '<div class="updated"><p>' . __('Settings saved.', 'nhanhvn') . '</p></div>';
    }

    // Xử lý lấy accessToken từ accessCode
    if ($tab === 'general' && isset($_POST['nhanhvn_get_access_token']) && check_admin_referer('nhanhvn_get_access_token')) {
      $access_code = sanitize_text_field($_POST['nhanhvn_access_code']);
      if (!empty($access_code)) {
        Nhanhvn_Sync_Log::log('oauth', 'manual', 'info', 'Manual accessCode input: ' . $access_code);
        $api = new Nhanhvn_Api();
        $response = $api->get_access_token($access_code);
        if (isset($response['accessToken'])) {
          update_option('nhanhvn_access_token', $response['accessToken']);
          update_option('nhanhvn_business_id', $response['businessId'] ?? '');
          update_option('nhanhvn_token_expires', time() + 31536000);
          Nhanhvn_Sync_Log::log('oauth', 'access_token', 'success', 'Access token retrieved successfully');
          echo '<div class="updated"><p>' . __('Access token retrieved successfully.', 'nhanhvn') . '</p></div>';
        } else {
          $error = $response['error'] ?? 'Unknown error';
          Nhanhvn_Sync_Log::log('oauth', 'access_token', 'error', 'Failed to retrieve access token: ' . $error);
          echo '<div class="error"><p>' . __('Failed to retrieve access token: ', 'nhanhvn') . esc_html($error) . '</p></div>';
        }
      } else {
        Nhanhvn_Sync_Log::log('oauth', 'manual', 'error', 'Access code is empty');
        echo '<div class="error"><p>' . __('Please enter an Access Code.', 'nhanhvn') . '</p></div>';
      }
    }

    // Xử lý đồng bộ sản phẩm
    if ($tab === 'products' && isset($_POST['nhanhvn_sync_products']) && check_admin_referer('nhanhvn_sync_products')) {
      $sync = new Nhanhvn_Sync(new Nhanhvn_Api());
      if (isset($_POST['product_ids']) && is_array($_POST['product_ids'])) {
        foreach ($_POST['product_ids'] as $product_id) {
          $product = wc_get_product($product_id);
          if ($product) {
            $sync->sync_product($product_id, $product);
          }
        }
        echo '<div class="updated"><p>' . __('Selected products synced.', 'nhanhvn') . '</p></div>';
      } else {
        $sync->manual_sync_products();
        echo '<div class="updated"><p>' . __('All products synced.', 'nhanhvn') . '</p></div>';
      }
    }

    // Xử lý đồng bộ kho hàng
    if ($tab === 'inventory' && isset($_POST['nhanhvn_sync_inventory']) && check_admin_referer('nhanhvn_sync_inventory')) {
      $sync = new Nhanhvn_Sync(new Nhanhvn_Api());
      if (isset($_POST['product_ids']) && is_array($_POST['product_ids'])) {
        foreach ($_POST['product_ids'] as $product_id) {
          $product = wc_get_product($product_id);
          if ($product) {
            $sync->sync_inventory($product_id, $product->get_stock_quantity());
          }
        }
        echo '<div class="updated"><p>' . __('Selected inventory synced.', 'nhanhvn') . '</p></div>';
      } else {
        $sync->manual_sync_inventory();
        echo '<div class="updated"><p>' . __('All inventory synced.', 'nhanhvn') . '</p></div>';
      }
    }

    // Xử lý kiểm tra phí vận chuyển
    if ($tab === 'shipping' && isset($_POST['nhanhvn_test_shipping']) && check_admin_referer('nhanhvn_test_shipping')) {
      $api = new Nhanhvn_Api();
      $data = [
        'carrier' => sanitize_text_field($_POST['carrier']),
        'destination' => [
          'city' => sanitize_text_field($_POST['city']),
          'address' => sanitize_text_field($_POST['address']),
        ],
        'weight' => floatval($_POST['weight']),
      ];
      $response = $api->calculate_shipping($data);
      if (isset($response['fee'])) {
        echo '<div class="updated"><p>' . sprintf(__('Shipping fee: %s', 'nhanhvn'), wc_price($response['fee'])) . '</p></div>';
      } else {
        echo '<div class="error"><p>' . __('Failed to calculate shipping fee: ', 'nhanhvn') . esc_html($response['error'] ?? 'Unknown error') . '</p></div>';
      }
    }

    // Xử lý webhook
    if ($tab === 'webhooks' && isset($_POST['nhanhvn_register_webhook']) && check_admin_referer('nhanhvn_webhook')) {
      $webhook = new Nhanhvn_Webhook(new Nhanhvn_Api());
      $result = $webhook->register_webhook();
      if (isset($result['error'])) {
        echo '<div class="error"><p>' . __('Failed to register webhook: ', 'nhanhvn') . esc_html($result['error']) . '</p></div>';
      } else {
        echo '<div class="updated"><p>' . __('Webhook registered successfully.', 'nhanhvn') . '</p></div>';
      }
    }

    if ($tab === 'webhooks' && isset($_POST['nhanhvn_unregister_webhook']) && check_admin_referer('nhanhvn_webhook')) {
      $webhook = new Nhanhvn_Webhook(new Nhanhvn_Api());
      $result = $webhook->unregister_webhook();
      if (isset($result['error'])) {
        echo '<div class="error"><p>' . __('Failed to unregister webhook: ', 'nhanhvn') . esc_html($result['error']) . '</p></div>';
      } else {
        echo '<div class="updated"><p>' . __('Webhook unregistered successfully.', 'nhanhvn') . '</p></div>';
      }
    }

    ?>
    <div class="wrap woocommerce">
      <h1><?php _e('Nhanh.vn Integration', 'nhanhvn'); ?></h1>
      <nav class="nav-tab-wrapper">
        <a href="<?php echo admin_url('admin.php?page=nhanhvn-sync&tab=general'); ?>"
          class="nav-tab <?php echo $tab === 'general' ? 'nav-tab-active' : ''; ?>"><?php _e('General Settings', 'nhanhvn'); ?></a>
        <a href="<?php echo admin_url('admin.php?page=nhanhvn-sync&tab=products'); ?>"
          class="nav-tab <?php echo $tab === 'products' ? 'nav-tab-active' : ''; ?>"><?php _e('Products', 'nhanhvn'); ?></a>
        <a href="<?php echo admin_url('admin.php?page=nhanhvn-sync&tab=inventory'); ?>"
          class="nav-tab <?php echo $tab === 'inventory' ? 'nav-tab-active' : ''; ?>"><?php _e('Inventory', 'nhanhvn'); ?></a>
        <a href="<?php echo admin_url('admin.php?page=nhanhvn-sync&tab=shipping'); ?>"
          class="nav-tab <?php echo $tab === 'shipping' ? 'nav-tab-active' : ''; ?>"><?php _e('Shipping', 'nhanhvn'); ?></a>
        <a href="<?php echo admin_url('admin.php?page=nhanhvn-sync&tab=webhooks'); ?>"
          class="nav-tab <?php echo $tab === 'webhooks' ? 'nav-tab-active' : ''; ?>"><?php _e('Webhooks', 'nhanhvn'); ?></a>
        <a href="<?php echo admin_url('admin.php?page=nhanhvn-sync&tab=logs'); ?>"
          class="nav-tab <?php echo $tab === 'logs' ? 'nav-tab-active' : ''; ?>"><?php _e('Logs', 'nhanhvn'); ?></a>
        <a href="<?php echo admin_url('admin.php?page=nhanhvn-sync&tab=help'); ?>"
          class="nav-tab <?php echo $tab === 'help' ? 'nav-tab-active' : ''; ?>"><?php _e('Help', 'nhanhvn'); ?></a>
      </nav>

      <?php
      switch ($tab) {
        case 'general':
          include NHANHVN_PLUGIN_DIR . 'templates/admin-settings.php';
          break;
        case 'products':
          include NHANHVN_PLUGIN_DIR . 'templates/admin-products.php';
          break;
        case 'inventory':
          include NHANHVN_PLUGIN_DIR . 'templates/admin-inventory.php';
          break;
        case 'shipping':
          include NHANHVN_PLUGIN_DIR . 'templates/admin-shipping.php';
          break;
        case 'webhooks':
          include NHANHVN_PLUGIN_DIR . 'templates/admin-webhooks.php';
          break;
        case 'logs':
          include NHANHVN_PLUGIN_DIR . 'templates/admin-logs.php';
          break;
        case 'help':
          include NHANHVN_PLUGIN_DIR . 'templates/admin-help.php';
          break;
      }
      ?>
    </div>
    <?php
  }

  public function enqueue_scripts()
  {
    wp_enqueue_style('nhanhvn-admin-css', NHANHVN_PLUGIN_URL . 'public/css/nhanhvn-admin.css', [], '2.3.4');
    wp_enqueue_script('nhanhvn-admin-js', NHANHVN_PLUGIN_URL . 'public/js/nhanhvn-admin.js', ['jquery'], '2.3.4', true);
    wp_localize_script('nhanhvn-admin-js', 'nhanhvn_admin', [
      'ajax_url' => admin_url('admin-ajax.php'),
      'nonce' => wp_create_nonce('nhanhvn_admin'),
      'confirm_sync' => __('Are you sure you want to sync?', 'nhanhvn'),
      'copy_success' => __('Copy successful!', 'nhanhvn'),
      'copy_failed' => __('Failed to copy.', 'nhanhvn'),
    ]);
  }
}
