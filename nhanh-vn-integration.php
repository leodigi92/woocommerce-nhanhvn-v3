<?php
/*
Plugin Name: Nhanh.vn Integration
Plugin URI: https://github.com/leodigi92/woocommerce-nhanhvn-v2
Description: Tích hợp WooCommerce với Nhanh.vn để đồng bộ sản phẩm, đơn hàng, khách hàng, tồn kho và vận chuyển.
Version: 2.3.4
Author: Leodigi92
Author URI: https://github.com/leodigi92
License: GPL2
Text Domain: nhanhvn
Domain Path: /languages
WC requires at least: 5.0
WC tested up to: 9.0
HPOS Compatibility: compatible
*/

if (!defined('ABSPATH')) {
  exit; // Exit if accessed directly
}

// Khai báo tương thích HPOS
add_action('before_woocommerce_init', function () {
  if (class_exists('\Automattic\WooCommerce\Utilities\FeaturesUtil')) {
    \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__, true);
  }
});

// Kiểm tra WooCommerce
if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
  add_action('admin_notices', function () {
    echo '<div class="error"><p>' . __('Nhanh.vn Integration requires WooCommerce to be installed and active.', 'nhanhvn') . '</p></div>';
  });
  return;
}

// Load Composer autoload
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
  require_once __DIR__ . '/vendor/autoload.php';
} else {
  add_action('admin_notices', function () {
    echo '<div class="error"><p>' . __('Nhanh.vn Integration requires Composer dependencies. Please run `composer install` in the plugin directory.', 'nhanhvn') . '</p></div>';
  });
  return;
}

// Định nghĩa hằng số
define('NHANHVN_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('NHANHVN_PLUGIN_URL', plugin_dir_url(__FILE__));

// Load file ngôn ngữ
add_action('plugins_loaded', function () {
  load_plugin_textdomain('nhanhvn', false, dirname(plugin_basename(__FILE__)) . '/languages/');
});

// Bao gồm các file cần thiết
require_once NHANHVN_PLUGIN_DIR . 'includes/api/class-nhanhvn-api.php';
require_once NHANHVN_PLUGIN_DIR . 'includes/models/class-nhanhvn-sync-log.php';
require_once NHANHVN_PLUGIN_DIR . 'includes/controllers/class-nhanhvn-sync.php';
require_once NHANHVN_PLUGIN_DIR . 'includes/controllers/class-nhanhvn-customer-sync.php';
require_once NHANHVN_PLUGIN_DIR . 'includes/controllers/class-nhanhvn-webhook.php';
require_once NHANHVN_PLUGIN_DIR . 'includes/controllers/class-nhanhvn-queue.php';
require_once NHANHVN_PLUGIN_DIR . 'admin/class-nhanhvn-admin.php';

class Nhanhvn_Plugin
{
  private $api;
  private $sync;
  private $customer_sync;
  private $shipping;
  private $webhook;
  private $queue;
  private $admin;

  public function __construct()
  {
    $this->init();
  }

  private function init()
  {
    add_action('plugins_loaded', [$this, 'initialize_plugin']);
  }

  public function initialize_plugin()
  {
    // Kiểm tra WooCommerce đã load
    if (!class_exists('WooCommerce')) {
      add_action('admin_notices', function () {
        echo '<div class="error"><p>' . __('Nhanh.vn Integration requires WooCommerce to be fully loaded.', 'nhanhvn') . '</p></div>';
      });
      return;
    }

    // Load shipping sau khi WooCommerce sẵn sàng
    require_once NHANHVN_PLUGIN_DIR . 'includes/controllers/class-nhanhvn-shipping.php';

    $this->api = new Nhanhvn_Api();
    $this->sync = new Nhanhvn_Sync($this->api);
    $this->customer_sync = new Nhanhvn_Customer_Sync($this->api);
    $this->shipping = new Nhanhvn_Shipping($this->api);
    $this->webhook = new Nhanhvn_Webhook($this->api);
    $this->queue = new Nhanhvn_Queue();
    $this->admin = new Nhanhvn_Admin();

    $this->init_hooks();
  }

  private function init_hooks()
  {
    // Hook kích hoạt và gỡ plugin
    register_activation_hook(__FILE__, [$this, 'activate']);
    register_deactivation_hook(__FILE__, [$this, 'deactivate']);

    // Hook WooCommerce
    add_action('woocommerce_order_status_changed', [$this->sync, 'sync_order'], 10, 3);
    add_action('woocommerce_product_updated', [$this->sync, 'sync_product'], 10, 2);
    add_action('user_register', [$this->customer_sync, 'sync_customer'], 10, 1);
    add_filter('woocommerce_shipping_methods', [$this->shipping, 'add_shipping_method']);
    add_action('rest_api_init', [$this->webhook, 'register_webhook_endpoint']);

    // Admin hooks
    if ($this->admin) {
      add_action('admin_menu', [$this->admin, 'add_admin_menu']);
      add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts']);
    }
  }

  public function enqueue_admin_scripts($hook)
  {
    if ($hook === 'woocommerce_page_nhanhvn-sync' && $this->admin) {
      $this->admin->enqueue_scripts();
    }
  }

  public function activate()
  {
    // Tạo bảng lưu log đồng bộ
    Nhanhvn_Sync_Log::create_table();

    // Đăng ký webhook và xử lý lỗi
    if (class_exists('Nhanhvn_Webhook')) {
      $webhook = new Nhanhvn_Webhook(new Nhanhvn_Api());
      $result = $webhook->register_webhook();
      if (isset($result['error'])) {
        Nhanhvn_Sync_Log::log('webhook_register', 'activate', 'error', 'Failed to register webhook on activation: ' . $result['error']);
      } else {
        Nhanhvn_Sync_Log::log('webhook_register', 'activate', 'success', 'Webhook registered successfully');
      }
    }
  }

  public function deactivate()
  {
    // Hủy đăng ký webhook
    if (class_exists('Nhanhvn_Webhook')) {
      $webhook = new Nhanhvn_Webhook(new Nhanhvn_Api());
      $result = $webhook->unregister_webhook();
      if (isset($result['error'])) {
        Nhanhvn_Sync_Log::log('webhook_unregister', 'deactivate', 'error', 'Failed to unregister webhook: ' . $result['error']);
      }
    }
  }
}

// Khởi tạo plugin
new Nhanhvn_Plugin();
