<?php
if (!defined('ABSPATH')) {
  exit;
}

class Nhanhvn_Queue
{
  public function add_task($type, $data)
  {
    if (function_exists('as_schedule_single_action')) {
      as_schedule_single_action(time(), 'nhanhvn_process_queue', [$type, $data], 'nhanhvn');
      Nhanhvn_Sync_Log::log('queue', $type, 'queued', 'Task added to queue');
    }
  }

  public function __construct()
  {
    add_action('nhanhvn_process_queue', [$this, 'process_task'], 10, 2);
  }

  public function process_task($type, $data)
  {
    $api = new Nhanhvn_Api();

    switch ($type) {
      case 'sync_product':
        $response = $api->sync_product($data);
        $status = isset($response['error']) ? 'error' : 'success';
        $message = isset($response['error']) ? $response['error'] : 'Product synced';
        Nhanhvn_Sync_Log::log('queue_product', $data['id'], $status, $message);
        break;
      case 'sync_order':
        $response = $api->create_order($data);
        $status = isset($response['error']) ? 'error' : 'success';
        $message = isset($response['error']) ? $response['error'] : 'Order synced';
        Nhanhvn_Sync_Log::log('queue_order', $data['id'], $status, $message);
        break;
    }
  }
}
