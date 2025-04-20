<?php
if (!defined('ABSPATH')) {
  exit;
}

class Nhanhvn_Shipping
{
  private $api;

  public function __construct($api)
  {
    $this->api = $api;
  }

  public function add_shipping_method($methods)
  {
    if (class_exists('WC_Shipping_Method')) {
      $methods['nhanhvn_shipping'] = 'Nhanhvn_Shipping_Method';
      if (get_option('nhanhvn_shipping_ghn_enabled')) {
        $methods['nhanhvn_shipping_ghn'] = 'Nhanhvn_Shipping_Method_GHN';
      }
      if (get_option('nhanhvn_shipping_ghtk_enabled')) {
        $methods['nhanhvn_shipping_ghtk'] = 'Nhanhvn_Shipping_Method_GHTK';
      }
    }
    return $methods;
  }
}

if (class_exists('WC_Shipping_Method')) {
  class Nhanhvn_Shipping_Method extends WC_Shipping_Method
  {
    protected $api;
    protected $carrier = 'nhanhvn';

    public function __construct($instance_id = 0)
    {
      parent::__construct($instance_id);
      $this->id = 'nhanhvn_shipping';
      $this->method_title = __('Nhanh.vn Shipping', 'nhanhvn');
      $this->method_description = __('Tích hợp vận chuyển với Nhanh.vn', 'nhanhvn');
      $this->supports = ['shipping-zones', 'instance-settings'];
      $this->api = new Nhanhvn_Api();

      $this->init();
    }

    protected function init()
    {
      $this->init_form_fields();
      $this->init_settings();
      $this->title = $this->get_option('title', __('Nhanh.vn Shipping', 'nhanhvn'));

      add_action('woocommerce_update_options_shipping_' . $this->id, [$this, 'process_admin_options']);
    }

    public function init_form_fields()
    {
      $this->form_fields = [
        'title' => [
          'title' => __('Title', 'nhanhvn'),
          'type' => 'text',
          'description' => __('Tên phương thức vận chuyển hiển thị cho khách hàng.', 'nhanhvn'),
          'default' => __('Nhanh.vn Shipping', 'nhanhvn'),
        ],
      ];
    }

    public function calculate_shipping($package = [])
    {
      $data = [
        'carrier' => $this->carrier,
        'destination' => [
          'city' => $package['destination']['city'],
          'address' => $package['destination']['address'],
        ],
        'weight' => nhanhvn_calculate_package_weight($package),
      ];

      $response = $this->api->calculate_shipping($data);
      if (isset($response['fee'])) {
        $rate = [
          'id' => $this->id,
          'label' => $this->title,
          'cost' => $response['fee'],
          'taxes' => false,
          'calc_tax' => 'per_order',
        ];
        $this->add_rate($rate);
        Nhanhvn_Sync_Log::log('shipping_calc', $this->id, 'success', 'Shipping fee calculated: ' . $response['fee']);
      } else {
        Nhanhvn_Sync_Log::log('shipping_calc', $this->id, 'error', 'Failed to calculate shipping fee: ' . (isset($response['error']) ? $response['error'] : 'Unknown error'));
      }
    }
  }

  class Nhanhvn_Shipping_Method_GHN extends Nhanhvn_Shipping_Method
  {
    public function __construct($instance_id = 0)
    {
      parent::__construct($instance_id);
      $this->id = 'nhanhvn_shipping_ghn';
      $this->method_title = __('GHN Shipping', 'nhanhvn');
      $this->method_description = __('Tích hợp vận chuyển với GHN qua Nhanh.vn', 'nhanhvn');
      $this->carrier = 'ghn';
      $this->init();
    }
  }

  class Nhanhvn_Shipping_Method_GHTK extends Nhanhvn_Shipping_Method
  {
    public function __construct($instance_id = 0)
    {
      parent::__construct($instance_id);
      $this->id = 'nhanhvn_shipping_ghtk';
      $this->method_title = __('GHTK Shipping', 'nhanhvn');
      $this->method_description = __('Tích hợp vận chuyển với GHTK qua Nhanh.vn', 'nhanhvn');
      $this->carrier = 'ghtk';
      $this->init();
    }
  }
}
