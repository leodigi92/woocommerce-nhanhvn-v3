<?php
if (!defined('ABSPATH')) {
  exit;
}

class Nhanhvn_Api
{
  private $base_url = 'https://open.nhanh.vn';
  private $graph_url = 'https://graph.nhanh.vn';

  public function get_access_token($code)
  {
    $app_id = get_option('nhanhvn_app_id');
    $secret_key = get_option('nhanhvn_secret_key');

    if (!$app_id || !$secret_key) {
      return ['error' => 'Missing App ID or Secret Key'];
    }

    $response = wp_remote_post($this->base_url . '/api/oauth/access_token', [
      'body' => [
        'appId' => $app_id,
        'secretKey' => $secret_key,
        'code' => $code,
      ],
      'timeout' => 30,
      'sslverify' => defined('NHANHVN_SSL_VERIFY') ? NHANHVN_SSL_VERIFY : true,
    ]);

    if (is_wp_error($response)) {
      $error = $response->get_error_message();
      Nhanhvn_Sync_Log::log('api_request', '/oauth/access_token', 'error', $error);
      return ['error' => $error];
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    if (isset($data['accessToken'])) {
      Nhanhvn_Sync_Log::log('api_request', '/oauth/access_token', 'success', 'Access token retrieved');
      return [
        'accessToken' => $data['accessToken'],
        'businessId' => $data['businessId'] ?? '',
      ];
    }

    $error = $data['message'] ?? 'Failed to retrieve access token';
    Nhanhvn_Sync_Log::log('api_request', '/oauth/access_token', 'error', $error);
    return ['error' => $error];
  }

  public function register_webhook($data)
  {
    $access_token = get_option('nhanhvn_access_token');
    $business_id = get_option('nhanhvn_business_id');

    if (!$access_token || !$business_id) {
      return ['error' => 'Missing access token or business ID'];
    }

    $response = wp_remote_post($this->base_url . '/api/webhook/register', [
      'headers' => [
        'Authorization' => 'Bearer ' . $access_token,
        'Content-Type' => 'application/json',
      ],
      'body' => json_encode([
        'version' => '2.0',
        'appId' => get_option('nhanhvn_app_id'),
        'businessId' => $business_id,
        'data' => $data,
      ]),
      'timeout' => 30,
      'sslverify' => defined('NHANHVN_SSL_VERIFY') ? NHANHVN_SSL_VERIFY : true,
    ]);

    if (is_wp_error($response)) {
      $error = $response->get_error_message();
      Nhanhvn_Sync_Log::log('api_request', '/webhook/register', 'error', $error);
      return ['error' => $error];
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);
    Nhanhvn_Sync_Log::log('api_request', '/webhook/register', 'success', json_encode($data));
    return $data;
  }

  public function unregister_webhook()
  {
    $access_token = get_option('nhanhvn_access_token');
    $business_id = get_option('nhanhvn_business_id');

    if (!$access_token || !$business_id) {
      return ['error' => 'Missing access token or business ID'];
    }

    $response = wp_remote_post($this->base_url . '/api/webhook/unregister', [
      'headers' => [
        'Authorization' => 'Bearer ' . $access_token,
        'Content-Type' => 'application/json',
      ],
      'body' => json_encode([
        'version' => '2.0',
        'appId' => get_option('nhanhvn_app_id'),
        'businessId' => $business_id,
      ]),
      'timeout' => 30,
      'sslverify' => defined('NHANHVN_SSL_VERIFY') ? NHANHVN_SSL_VERIFY : true,
    ]);

    if (is_wp_error($response)) {
      $error = $response->get_error_message();
      Nhanhvn_Sync_Log::log('api_request', '/webhook/unregister', 'error', $error);
      return ['error' => $error];
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);
    Nhanhvn_Sync_Log::log('api_request', '/webhook/unregister', 'success', json_encode($data));
    return $data;
  }

  public function calculate_shipping($data)
  {
    $access_token = get_option('nhanhvn_access_token');
    $business_id = get_option('nhanhvn_business_id');

    if (!$access_token || !$business_id) {
      return ['error' => 'Missing access token or business ID'];
    }

    $response = wp_remote_post($this->graph_url . '/api/shipping/calculate', [
      'headers' => [
        'Authorization' => 'Bearer ' . $access_token,
        'Content-Type' => 'application/json',
      ],
      'body' => json_encode([
        'version' => '2.0',
        'appId' => get_option('nhanhvn_app_id'),
        'businessId' => $business_id,
        'data' => $data,
      ]),
      'timeout' => 30,
      'sslverify' => defined('NHANHVN_SSL_VERIFY') ? NHANHVN_SSL_VERIFY : true,
    ]);

    if (is_wp_error($response)) {
      $error = $response->get_error_message();
      Nhanhvn_Sync_Log::log('api_request', '/shipping/calculate', 'error', $error);
      return ['error' => $error];
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);
    Nhanhvn_Sync_Log::log('api_request', '/shipping/calculate', 'success', json_encode($data));
    return $data;
  }
}
