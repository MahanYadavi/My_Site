<?php

/**
 * Duplicator schedule success mail
 *
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

defined("ABSPATH") or die("");

/**
 * Variables
 *
 * @var \Duplicator\Core\Controllers\ControllersManager $ctrlMng
 * @var \Duplicator\Core\Views\TplMng  $tplMng
 * @var array<string, mixed> $tplData
 */
?>
<p><?php echo $tplData['messageTitle']; ?></p>
<p>
    <strong><?php DUP_PRO_U::_e('Package Name') ?>: </strong><?php echo $tplData['packageName']; ?><br/>
    <strong><?php DUP_PRO_U::_e('Package ID') ?>: </strong><?php echo $tplData['packageID']; ?><br/>
    <strong><?php DUP_PRO_U::_e('Date') ?>: </strong><?php echo date_i18n('Y-m-d H:i:s'); ?><br/>
    <strong><?php DUP_PRO_U::_e('Schedule') ?>: </strong><?php echo $tplData['scheduleName']; ?>
</p>

<?php if ($tplData['success']) : ?>
<p>
    <strong><?php DUP_PRO_U::_e('Number of Files') ?>: </strong><?php echo $tplData['fileCount']; ?><br/>
    <strong><?php DUP_PRO_U::_e('Package size') ?>: </strong><?php echo $tplData['packageSize']; ?>
</p>
<p>
    <strong><?php DUP_PRO_U::_e('Number of tables') ?>: </strong><?php echo $tplData['tableCount']; ?><br/>
    <strong><?php DUP_PRO_U::_e('DB dump size') ?>: </strong><?php echo $tplData['sqlSize']; ?>
</p>
<?php endif; ?>

<p>
    <strong><?php DUP_PRO_U::_e('Storages') ?>: </strong>
    <?php foreach ($tplData['storageNames'] as $storageName) : ?>
        <br/> - <?php echo $storageName; ?>
    <?php endforeach; ?>
</p>
<p>
    <?php echo sprintf(
        DUP_PRO_U::__('To go to the "Packages" screen <a href="%s" target="_blank">click here</a>.'),
        $tplData['packagesLink']
    ); ?>
</p>
<?php if ($tplData['logExists']) : ?>
<p>
    <?php DUP_PRO_U::_e('Log is attached.'); ?>
</p>
<?php endif; ?>
