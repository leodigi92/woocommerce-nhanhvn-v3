<?php
if (!defined('ABSPATH')) {
  exit;
}

class Nhanhvn_Webhook
{
  private $api;

  public function __construct($api)
  {
    $this->api = $api;
  }

  public function register_webhook()
  {
    $webhook_url = get_option('nhanhvn_webhook_url');

    // Tự động thiết lập webhook URL nếu chưa có
    if (empty($webhook_url)) {
      $webhook_url = rest_url('nhanhvn/v1/webhook');
      update_option('nhanhvn_webhook_url', $webhook_url);
      Nhanhvn_Sync_Log::log('webhook', 'setup', 'success', 'Webhook callback URL set to default: ' . $webhook_url);
    }

    $verify_token = get_option('nhanhvn_webhook_verify_token');
    if (empty($verify_token)) {
      $verify_token = wp_generate_password(32, false);
      update_option('nhanhvn_webhook_verify_token', $verify_token);
      Nhanhvn_Sync_Log::log('webhook', 'setup', 'success', 'Webhook verify token generated');
    }

    $data = [
      'webhookUrl' => $webhook_url,
      'webhooksVerifyToken' => $verify_token,
      'events' => [
        '101' => 'productAdd',
        '102' => 'productUpdate',
        '110' => 'inventoryChange',
        '202' => 'orderUpdate',
      ],
    ];

    $response = $this->api->register_webhook($data);
    if (isset($response['error'])) {
      Nhanhvn_Sync_Log::log('webhook', 'register', 'error', 'Failed to register webhook: ' . $response['error']);
    } else {
      Nhanhvn_Sync_Log::log('webhook', 'register', 'success', 'Webhook registered successfully');
    }
    return $response;
  }

  public function unregister_webhook()
  {
    $response = $this->api->unregister_webhook();
    if (isset($response['error'])) {
      Nhanhvn_Sync_Log::log('webhook', 'unregister', 'error', 'Failed to unregister webhook: ' . $response['error']);
    } else {
      Nhanhvn_Sync_Log::log('webhook', 'unregister', 'success', 'Webhook unregistered successfully');
    }
    return $response;
  }

  public function register_webhook_endpoint()
  {
    register_rest_route('nhanhvn/v1', '/webhook', [
      'methods' => 'POST',
      'callback' => [$this, 'handle_webhook'],
      'permission_callback' => '__return_true',
    ]);
  }

  public function handle_webhook($request)
  {
    $verify_token = get_option('nhanhvn_webhook_verify_token');
    $body = $request->get_json_params();

    if (empty($verify_token)) {
      Nhanhvn_Sync_Log::log('webhook', 'handle', 'error', 'Webhook verify token not set');
      return new WP_Error('missing_token', 'Webhook verify token not set', ['status' => 403]);
    }

    if (!isset($body['webhooksVerifyToken']) || $body['webhooksVerifyToken'] !== $verify_token) {
      Nhanhvn_Sync_Log::log('webhook', 'handle', 'error', 'Invalid webhook verify token');
      return new WP_Error('invalid_token', 'Invalid webhook verify token', ['status' => 403]);
    }

    $event = $body['event'] ?? 'unknown';
    $data = $body['data'] ?? [];

    Nhanhvn_Sync_Log::log('webhook', $event, 'success', json_encode($data));

    // Xử lý các sự kiện webhook
    switch ($event) {
      case 'productAdd':
      case 'productUpdate':
        // Đồng bộ sản phẩm
        $this->sync->sync_product_from_webhook($data);
        break;
      case 'inventoryChange':
        // Đồng bộ tồn kho
        $this->sync->sync_inventory_from_webhook($data);
        break;
      case 'orderUpdate':
        // Đồng bộ đơn hàng
        $this->sync->sync_order_from_webhook($data);
        break;
    }

    return ['status' => 'success'];
  }
}
