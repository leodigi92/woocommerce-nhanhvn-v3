jQuery(document).ready(function($) {
    // Chọn tất cả sản phẩm
    $('#nhanhvn-select-all').on('change', function() {
        $('.nhanhvn-product-checkbox').prop('checked', this.checked);
    });

    // Xác nhận đồng bộ
    $('.nhanhvn-sync-button').on('click', function(e) {
        if (!confirm(nhanhvn_admin.confirm_sync)) {
            e.preventDefault();
        }
    });

    // Sao chép Redirect URL
    $('#nhanhvn-copy-redirect-url').on('click', function() {
        var redirectUrl = $('#nhanhvn_redirect_url').val();
        navigator.clipboard.writeText(redirectUrl).then(function() {
            alert(nhanhvn_admin.copy_success);
        }, function() {
            alert(nhanhvn_admin.copy_failed);
        });
    });

    // Lọc log
    $('#nhanhvn-log-filter').on('submit', function(e) {
        e.preventDefault();
        var type = $('#nhanhvn-log-type').val();
        var status = $('#nhanhvn-log-status').val();
        var date = $('#nhanhvn-log-date').val();

        $.ajax({
            url: nhanhvn_admin.ajax_url,
            type: 'POST',
            data: {
                action: 'nhanhvn_filter_logs',
                nonce: nhanhvn_admin.nonce,
                type: type,
                status: status,
                date: date
            },
            success: function(response) {
                if (response.success) {
                    $('#nhanhvn-log-table tbody').html(response.data.html);
                } else {
                    alert('Lỗi khi lọc nhật ký: ' + response.data.message);
                }
            }
        });
    });
});
