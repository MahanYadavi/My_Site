<?php
defined("ABSPATH") or die("");

DUP_PRO_Handler::init_error_handler();

global $wpdb;

//COMMON HEADER DISPLAY
//require_once(DUPLICATOR____PATH . '/assets/js/javascript.php');

$_REQUEST['action'] = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'main';

switch ($_REQUEST['action']) {
    case 'detail':
        $current_view = 'detail';
        break;
    default:
        $current_view = 'main';
        break;
}

$nonce = wp_create_nonce('duplicator_pro_download_installer');
?>

<script>
    jQuery(document).ready(function ($)
    {
        DupPro.Pack.DownloadFile = function (url, fileName='')
        {
            var link = document.createElement('a');
            link.className = "dpro-dnload-menu-item";
            link.href = url;
            if (fileName !== '') {
                link.download = fileName;
            }
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            return false;
        };
    });
</script>

<?php
switch ($current_view) {
    case 'main':
        include(DUPLICATOR____PATH . '/views/packages/main/controller.php');
        break;
    case 'detail':
        include(DUPLICATOR____PATH . '/views/packages/details/controller.php');
        break;
}
