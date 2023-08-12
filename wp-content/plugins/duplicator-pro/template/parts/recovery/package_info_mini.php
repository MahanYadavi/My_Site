<?php

/**
 * Duplicator package row in table packages list
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
 * @var \DUP_PRO_Package $package
 */
$package = $tplData['package'];

$timeDiff = sprintf(
    _x('%s ago', '%s represents the time diff, eg. 2 days', 'duplicator-pro'),
    $package->getPackageLife('human')
);

?>
<table>
    <tr>
        <td><b><?php esc_html_e('Package', 'duplicator-pro'); ?>:</b></td>
        <td><?php echo esc_html($package->Name); ?></td>
    </tr>
    <tr>
        <td><b><?php esc_html_e('Created', 'duplicator-pro'); ?>:</b>&nbsp; </td>
        <td>
            <?php echo $package->Created; ?>&nbsp;-&nbsp;<i><?php echo $timeDiff; ?></i>
        </td>
    </tr>
</table>
