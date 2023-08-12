<?php

/**
 * Duplicator page header
 *
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

use Duplicator\Core\Controllers\SubMenuItem;

defined("ABSPATH") or die("");

/**
 * Variables
 *
 * @var \Duplicator\Core\Controllers\ControllersManager $ctrlMng
 * @var \Duplicator\Core\Views\TplMng  $tplMng
 * @var array<string, mixed> $tplData
 */

if (empty($tplData['menuItemsL3'])) {
    return;
}

/** @var SubMenuItem[] */
$items = $tplData['menuItemsL3'];
?>
<div class="dup-sub-tabs">
    <?php
    foreach ($items as $item) {
        $id      = 'dup-submenu-l3-' . $tplData['currentLevelSlugs'][0] . '-' . $tplData['currentLevelSlugs'][1] . '-' . $item->slug;
        $classes = array('dup-submenu-l3');
        ?>
        <span id="<?php echo esc_attr($id); ?>" class="dup-sub-tab-item <?php echo ($item->active ? 'dup-sub-tab-active' : ''); ?>" >
            <?php if ($item->active) { ?>
                <b><?php echo esc_html($item->label); ?></b> 
            <?php } else { ?>
                <a href="<?php echo esc_url($item->link); ?>" class="<?php echo implode(' ', $classes); ?>" >
                    <span><?php echo esc_html($item->label); ?></span>
                </a>
            <?php } ?>
        </span>
    <?php } ?>
</div>
