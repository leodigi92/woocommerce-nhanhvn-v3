<?php
if (!defined('ABSPATH')) {
  exit;
}

class Nhanhvn_Customer_Sync
{
  private $api;

  public function __construct($api)
  {
    $this->api = $api;
  }

  public function sync_customer($user_id)
  {
    $user = get_userdata($user_id);
    if (!$user) {
      Nhanhvn_Sync_Log::log('customer_sync', $user_id, 'error', 'User not found');
      return;
    }

    $data = [
      'id' => $user->ID,
      'name' => $user->display_name,
      'email' => $user->user_email,
      'phone' => get_user_meta($user->ID, 'billing_phone', true),
      'address' => get_user_meta($user->ID, 'billing_address_1', true),
    ];

    $response = $this->api->sync_customer($data);
    if (isset($response['error'])) {
      Nhanhvn_Sync_Log::log('customer_sync', $user_id, 'error', $response['error']);
    } else {
      Nhanhvn_Sync_Log::log('customer_sync', $user_id, 'success', 'Customer synced successfully');
    }
  }
}
