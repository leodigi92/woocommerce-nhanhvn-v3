<?php
if (!defined('ABSPATH')) {
  exit;
}

class Nhanhvn_Sync_Log
{
  public static function create_table()
  {
    global $wpdb;
    $table_name = $wpdb->prefix . 'nhanhvn_sync_log';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            type varchar(50) NOT NULL,
            item_id varchar(50) NOT NULL,
            status varchar(20) NOT NULL,
            message text NOT NULL,
            sync_time datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);
  }

  public static function log($type, $item_id, $status, $message)
  {
    global $wpdb;
    $table_name = $wpdb->prefix . 'nhanhvn_sync_log';

    $wpdb->insert(
      $table_name,
      [
        'type' => sanitize_text_field($type),
        'item_id' => sanitize_text_field($item_id),
        'status' => sanitize_text_field($status),
        'message' => sanitize_textarea_field($message),
      ],
      ['%s', '%s', '%s', '%s']
    );

    // Ghi log vào WooCommerce
    $logger = wc_get_logger();
    $logger->log($status === 'error' ? 'error' : 'info', "{$type} {$item_id}: {$message}", ['source' => 'nhanhvn']);
  }
}
