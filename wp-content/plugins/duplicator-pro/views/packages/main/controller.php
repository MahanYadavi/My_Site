<?php

defined("ABSPATH") or die("");

use Duplicator\Libs\Snap\SnapURL;

DUP_PRO_U::hasCapability('export');

require_once(DUPLICATOR____PATH . '/classes/ui/class.ui.dialog.php');

$inner_page = isset($_REQUEST['inner_page']) ? sanitize_text_field($_REQUEST['inner_page']) : 'list';

// nonce checking
switch ($inner_page) {
    case 'new1':
        if (!wp_verify_nonce($_GET['_wpnonce'], 'new1-package')) {
            die('Security issue');
        }
        break;
    case 'new2':
        if (!wp_verify_nonce($_GET['_wpnonce'], 'new2-package')) {
            die('Security issue');
        }
        break;
}
?>

<style>
    /*WIZARD TABS */
    div#dup-wiz {padding:0px; margin:0;  }
    div#dup-wiz-steps {margin:0; padding:0;  clear:both; font-size:13px; min-width:390px; }
    div#dup-wiz-title {padding:2px 0px 0px 0px; font-size:14px; clear: both; display:none}
    #dup-wiz a { position:relative; display:block; width:auto; min-width:90px; min-height:22px; margin-right:6px; padding:1px 8px 1px 8px; float:left; line-height:22px;
                 color:#000; background:#E4E4E4; border-radius:2px;  border:1px solid #999; text-align: center }
    #dup-wiz .active-step a {color:#fff; background:#A8A8A8; font-weight: bold; border:1px solid #666; box-shadow: 3px 3px 3px 0 #999; letter-spacing:0.05em;}
    #dup-wiz .completed-step a {color:#E1E1E1; background:#BBBBBB;  border:1px solid #BEBEBE; }
    /*Footer */
    div.dup-button-footer input {min-width: 105px}
    div.dup-button-footer {padding: 1px 10px 0px 0px; text-align: right}
</style>

<?php

switch ($inner_page) {
    case 'list':
        include(DUPLICATOR____PATH . '/views/packages/main/packages.php');
        break;
    case 'new1':
        include(DUPLICATOR____PATH . '/views/packages/main/s1.setup0.base.php');
        break;
    case 'new2':
        include(DUPLICATOR____PATH . '/views/packages/main/s2.scan1.base.php');
        break;
}