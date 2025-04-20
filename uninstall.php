<?php
if (!defined('WP_UNINSTALL_PLUGIN')) {
  exit;
}

// Xóa bảng log đồng bộ
global $wpdb;
$table_name = $wpdb->prefix . 'nhanhvn_sync_log';
$wpdb->query("DROP TABLE IF EXISTS $table_name");

// Xóa tùy chọn
delete_option('nhanhvn_api_key');
