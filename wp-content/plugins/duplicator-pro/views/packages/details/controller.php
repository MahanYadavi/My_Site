<?php
defined("ABSPATH") or die("");

DUP_PRO_U::hasCapability('manage_options');
global $wpdb;

$current_tab = isset($_REQUEST['tab']) ? sanitize_text_field($_REQUEST['tab']) : 'detail';
$package_id  = isset($_REQUEST["id"])   ? sanitize_text_field($_REQUEST["id"]) : 0;

if (!empty($_GET['_wpnonce'])) {
    switch ($current_tab) {
        case 'detail':
            if (!wp_verify_nonce($_GET['_wpnonce'], 'package-detail')) {
                die(
                    'Security Check Invalid: Please refresh this page to properly sync the authorization token.'
                    . ' If the problem persists please log out and log back in.'
                );
            }
            break;
        case 'transfer':
            if (!wp_verify_nonce($_GET['_wpnonce'], 'package-transfer')) {
                die(
                    'Security Check Invalid: Please refresh this page to properly sync the authorization token.'
                    . ' If the problem persists please log out and log back in.'
                );
            }
            break;
    }
}


$package       = DUP_PRO_Package::get_by_id($package_id);
$package_found = is_object($package) ? true : false;

if ($package_found) {
    $enable_transfer_tab = (
        $package->getLocalPackageFilePath(DUP_PRO_Package_File_Type::Installer) !== false &&
        $package->getLocalPackageFilePath(DUP_PRO_Package_File_Type::Archive) !== false
    );
    $error_display       = ($package->Status == DUP_PRO_PackageStatus::ERROR) ? 'default' : 'none';
    $err_link_pack       = $package->get_log_url();
} else {
    $enable_transfer_tab = false;
    $error_display       = 'none';
    $err_link_pack       = '';
}

$err_link_log    = "<a target='_blank' href=\"$err_link_pack\">" . DUP_PRO_U::__('package log') . '</a>';
$err_link_faq    = '<a target="_blank" href="https://snapcreek.com/duplicator/docs/faqs-tech/">' . DUP_PRO_U::__('FAQ pages') . '</a>';
$err_link_ticket = '<a target="_blank" href="https://snapcreek.com/ticket/index.php?a=add&category=1">' . DUP_PRO_U::__('help ticket') . '</a>';

?>

<style>
    .narrow-input { width: 80px; }
    .wide-input {width: 400px; } 
     table.form-table tr td { padding-top: 25px; }
     div.dup-packages {float:right; margin:-25px 10px 0 0; }
     #dpro-error { display: <?php echo $error_display; ?>;  margin:5px 0; text-align:center; font-style:italic}
</style>

<?php if (! $package_found) : ?>
    <br/><br/>
    <div id='dpro-error' class="error">
        <p>
            <?php echo sprintf(DUP_PRO_U::__("Unable to find package id %d.  The package does not exist or was deleted."), $package_id); ?> <br/>
        </p>
    </div>
    <?php
    die();
endif;
?>

<h2 class="nav-tab-wrapper">  
    <a href="?page=duplicator-pro&action=detail&tab=detail&id=<?php echo $package_id ?>" class="nav-tab <?php echo ($current_tab == 'detail') ? 'nav-tab-active' : '' ?>"> <?php DUP_PRO_U::esc_html_e('Details'); ?></a> 
    <a <?php if ($enable_transfer_tab === false) {
        echo 'onclick="DupPro.Pack.TransferDisabled(); return false;"';
       } ?> href="?page=duplicator-pro&action=detail&tab=transfer&id=<?php echo absint($package_id); ?>" class="nav-tab <?php echo ($current_tab == 'transfer') ? 'nav-tab-active' : '' ?>"> <?php DUP_PRO_U::esc_html_e('Transfer'); ?></a>      
</h2>
<div class="dup-packages">
    <a href="admin.php?page=duplicator-pro">[<?php DUP_PRO_U::esc_html_e('Packages'); ?>]</a>
</div>

<div id='dpro-error' class="error">
    <p>
        <b><?php echo DUP_PRO_U::__('Error encountered building package, please review ') . $err_link_log . DUP_PRO_U::__(' for details.')  ; ?> </b><br/>
        <?php echo DUP_PRO_U::__('For more help read the ') . $err_link_faq . DUP_PRO_U::__(' or submit a ') . $err_link_ticket; ?>.
    </p>
</div>

<?php
switch ($current_tab) {
    case 'detail':
        include(DUPLICATOR____PATH . '/views/packages/details/detail.php');
        break;
    case 'transfer':
        include(DUPLICATOR____PATH . '/views/packages/details/transfer.php');
        break;
}
?>

<?php
    $alert1          = new DUP_PRO_UI_Dialog();
    $alert1->title   = DUP_PRO_U::__('Transfer Error');
    $alert1->message = DUP_PRO_U::__('No package in default location so transfer is disabled.');
    $alert1->initAlert();
?>
<script>
    DupPro.Pack.TransferDisabled = function() {
        <?php $alert1->showAlert(); ?>
    }
</script>
