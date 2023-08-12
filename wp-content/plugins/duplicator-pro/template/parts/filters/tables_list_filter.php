<?php

/**
 * Duplicator package row in table packages list
 *
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

use Duplicator\Libs\Snap\SnapWP;

defined("ABSPATH") or die("");

/**
 * Variables
 *
 * @var \Duplicator\Core\Controllers\ControllersManager $ctrlMng
 * @var \Duplicator\Core\Views\TplMng  $tplMng
 * @var array<string, mixed> $tplData
 * @var \wpdb $wpdb
 */

$dbFilterOn        = isset($tplData['dbFilterOn']) ? $tplData['dbFilterOn'] :  false;
$dbPrefixFilter    = isset($tplData['dbPrefixFilter']) ? $tplData['dbPrefixFilter'] :  false;
$dbPrefixSubFilter = isset($tplData['dbPrefixSubFilter']) ? $tplData['dbPrefixSubFilter'] :  false;
$tSelected         = isset($tplData['tablesSlected']) ? $tplData['tablesSlected'] :  array();
$dbTableCount      = 1;

global $wpdb;

$toolTipPrefixFilterContent = __(
    'By enabling this option all tables that do not start with ' .
    'the prefix <b>"' . esc_html($wpdb->prefix) . '"</b> are excluded from the package.  ' .
    'This option is useful for multiple WordPress installs in the same database or if several applications ' .
    'are installed in the same database. ',
    'duplicator-pro'
);

$toolTipSubsiteFilterContent = __(
    'Enabling this option excludes all tables associated with deleted sites from the package.<br><br>' .
    'When deleting a site in a multisite; WordPress deletes the tables of items related to the core, however it is not ' .
    'assumed that the tables of third party plugins are removed.  With a multisite with a large number of deleted sites the ' .
    'database may be full of unused tables.  With this option only the tables of currently existing sites will be included in the backup.',
    'duplicator-pro'
);

?>

<div class="dup-package-hdr-1">
    <?php DUP_PRO_U::esc_html_e("Filters") ?>
</div>

<div class="dup-form-item">
    <span class="title">
        <?php DUP_PRO_U::esc_html_e("Enable Filters") ?>:
    </span>
    <span class="input">
        <label>
            <input type="checkbox" id="dbfilter-on" name="dbfilter-on" <?php checked($dbFilterOn); ?> >
            <?php DUP_PRO_U::esc_html_e("Allow database tables to be excluded") ?>
        </label>
        <i class="fas fa-question-circle fa-sm"
        data-tooltip-title="<?php DUP_PRO_U::esc_attr_e("Database Table Filters"); ?>"
        data-tooltip="<?php DUP_PRO_U::esc_attr_e('Table filters allow you to ignore specific tables from a database. '
        . 'When creating a package only include the data you want and need.  This helps to speed up the build process '
        . 'and keep your backups simple and clean.  Tables that are checked will be excluded.  Tables with an * in red '
        . 'are core WordPress tables and should typically not be excluded.'); ?>"> <br/>
        </i>
    </span>
</div>

<div class="dup-form-item">
    <span class="title">
        <?php DUP_PRO_U::esc_html_e("Table Prefixes") ?>:
    </span>
    <span class="input">
        <label>
            <input 
                type="checkbox" 
                id="db-prefix-filter" 
                name="db-prefix-filter" 
                <?php checked($dbPrefixFilter); ?> 
                <?php disabled(!$dbFilterOn); ?> 
                data-prefix-value="<?php echo esc_attr($wpdb->prefix); ?>" />
            <?php
                DUP_PRO_U::esc_html_e('Filter tables without current WordPress prefix');
                echo isset($wpdb->prefix) ?  "&nbsp;<i>({$wpdb->prefix})</i>&nbsp;" : '';
            ?>
        </label>
        <i class="fas fa-question-circle fa-sm"
        data-tooltip-title="<?php DUP_PRO_U::esc_attr_e("WordPress Prefix Filters"); ?>"
        data-tooltip="<?php echo esc_attr($toolTipPrefixFilterContent); ?>"> <br/>
        </i>
    </span>
</div>

<?php if (is_multisite()) { ?>
    <div class="dup-form-item">
        <span class="title">
            <?php DUP_PRO_U::esc_html_e("Subsites") ?>:
        </span>
        <span class="input">
            <label>
                <input 
                    type="checkbox" 
                    id="db-prefix-sub-filter" 
                    name="db-prefix-sub-filter" 
                    <?php checked($dbPrefixSubFilter); ?>
                    <?php disabled(!$dbFilterOn); ?>
                > 
                <?php DUP_PRO_U::esc_html_e("Filter/Hide Tables of Deleted Multisite-Subsites") ?>
            </label>
            <i class="fas fa-question-circle fa-sm"
            data-tooltip-title="<?php DUP_PRO_U::esc_attr_e("Multisite-Subsite Filters"); ?>"
            data-tooltip="<?php echo esc_attr($toolTipSubsiteFilterContent); ?>">
            </i>
        </span>
    </div>
<?php } ?>

<div id="dup-db-filter-items" >
    <div class="dup-db-filter-buttons" >
        <span id="dbnone" class="link-style dup-db-filter-none">
            <i class="far fa-minus-square fa-lg"  title="<?php DUP_PRO_U::esc_html_e('Uncheck All Checkboxes'); ?>"></i>
        </span>&nbsp;
        <span id="dball" class="link-style dup-db-filter-all">
            <i class="far fa-check-square fa-lg" title="<?php DUP_PRO_U::esc_html_e('Check All Checkboxes'); ?>"></i>
        </span>
    </div>
    <div id="dup-db-tables-exclude-wrapper" >
        <div id="dup-db-tables-exclude" >
            <input type="hidden" id="dup-db-tables-lists" name="dbtables-list" value="" >
            <?php
            $substesIds = SnapWP::getSitesIds();
            foreach (DUP_PRO_DB::getTablesList() as $table) {
                $info    = SnapWP::getTableInfoByName($table, $wpdb->prefix);
                $classes = array('table-item');

                if ($info['isCore']) {
                    $classes[] = 'core-table';
                    $core_note = '*';

                    if ($info['subsiteId'] > 0) {
                        $classes[] = ' subcore-table-' . ($info['subsiteId'] % 2);
                    }
                } else {
                    $core_note = '';
                }
                $dbTableCount++;
                $cboxClasses = array('dup-pseudo-checkbox');
                $checked     = in_array($table, $tSelected);

                if ($info['subsiteId'] > 1 && !in_array($info['subsiteId'], $substesIds)) {
                    $classes[] = 'no-subsite-exists';
                    if ($dbPrefixSubFilter) {
                        $cboxClasses[] = 'disabled';
                        $checked       = true;
                    }
                }

                if ($info['havePrefix'] == false) {
                    $classes[] = 'no-prefix-table';
                    if ($dbPrefixFilter) {
                        $cboxClasses[] = 'disabled';
                        $checked       = true;
                    }
                }

                if ($checked) {
                    $cboxClasses[] = 'checked';
                }
                ?>
                <label class="<?php echo implode(' ', $classes); ?>">
                    <span
                        class="<?php echo implode(' ', $cboxClasses); ?>"
                        aria-checked="<?php echo $checked ? "true" : "false";?>"
                        role="checkbox"
                        data-value="<?php echo esc_attr($table); ?>">
                    </span>
                    &nbsp;<span><?php echo $table . $core_note; ?></span>
                </label>
            <?php } ?>
        </div>
        <div id="dup-db-filter-items-no-filters">
            <div>
                <?php _e('- Table Filters Disabled -', 'duplicator-pro'); ?><br>
                <?php printf(__('All %d tables will be included in this build.', 'duplicator-pro'), $dbTableCount); ?>
            </div>
        </div>
    </div>    
    <div class="dup-tabs-opts-help">
        <?php
            echo wp_kses(DUP_PRO_U::__("Checked tables will be <u>excluded</u> from the database script. "), array('u' => array()));
            DUP_PRO_U::esc_html_e("Excluding certain tables can cause your site or plugins to not work correctly after install!");
            echo '<br>';
            echo '<i class="core-table-info"> ';
            DUP_PRO_U::esc_html_e(
                "Use caution when excluding tables! It is highly recommended to not exclude WordPress core tables in red with an *, " .
                "unless you know the impact."
            );
            echo '</i>';
            ?>
    </div>
</div>

<script>
jQuery(function($) 
{
    /* METHOD: Toggle Database table filter red icon */
    DupPro.Pack.ToggleDBFiltersRedIcon = function() {
        if (
            $("#dbfilter-on").is(':checked')
        ) {
            $('#dup-archive-filter-db').show();
            $('#db-prefix-filter').prop('disabled', false);
            $('#db-prefix-sub-filter').prop('disabled', false);
        } else {
            $('#dup-archive-filter-db').hide();
            $('#db-prefix-filter').prop('disabled', true);
            $('#db-prefix-sub-filter').prop('disabled', true);
        }
    }

    DupPro.Pack.ToggleDBFilters = function () 
    {
        var filterItems = $('#dup-db-filter-items');

        if (
            $("#dbfilter-on").is(':checked')
        ) {
            filterItems.removeClass('disabled');
            $('#dup-db-filter-items-no-filters').hide();
        } else {
            filterItems.addClass('disabled');
            $('#dup-db-filter-items-no-filters').show();
        }

        DupPro.Pack.ToggleDBFiltersRedIcon();
    };

    DupPro.Pack.FillExcludeTablesList = function () {
        let values = $("#dup-db-tables-exclude .dup-pseudo-checkbox.checked")
            .map(function() {
                return this.getAttribute('data-value');
            })
            .get()
            .join();

        $('#dup-db-tables-lists').val(values);
    };

    DupPro.Pack.ToggleNoPrefixTables = function (removeCheckOnEnable = true) {
        let checkNode = $('#db-prefix-filter');
        let display = !checkNode.is(":checked");

        $("#dup-db-tables-exclude .no-prefix-table").each(function() {
            let checkBox = $(this).find(".dup-pseudo-checkbox").first();
            if (display) {
                checkBox.removeClass('disabled');
                if(removeCheckOnEnable) {
                    checkBox.removeClass("checked");
                }
            } else {
                checkBox
                    .addClass('disabled')
                    .addClass("checked");
            }
        });

        DupPro.Pack.ToggleDBFiltersRedIcon();
    }

    DupPro.Pack.ToggleNoSubsiteExistsTables = function (removeCheckOnEnable = true) {
        let checkNode = $('#db-prefix-sub-filter');
        let display = !checkNode.is(":checked");

        $("#dup-db-tables-exclude .no-subsite-exists").each(function() {
            let checkBox = $(this).find(".dup-pseudo-checkbox").first();
            if (display) {
                checkBox.removeClass('disabled');
                if(removeCheckOnEnable) {
                    checkBox.removeClass("checked");
                }
            } else {
                checkBox
                    .addClass('disabled')
                    .addClass("checked");
            }
        });

        DupPro.Pack.ToggleDBFiltersRedIcon();
    }
});

jQuery(document).ready(function($) 
{
    let tablesToExclude = $("#dup-db-tables-exclude");

    $('.dup-db-filter-none').click(function () {
        tablesToExclude.find(".dup-pseudo-checkbox.checked").removeClass("checked");
    });

    $('.dup-db-filter-all').click(function () {
        tablesToExclude.find(".dup-pseudo-checkbox:not(.checked)").addClass("checked");
    });

    $('#db-prefix-sub-filter').change(DupPro.Pack.ToggleNoSubsiteExistsTables);
    $('#db-prefix-filter').change(DupPro.Pack.ToggleNoPrefixTables);
    $('#dbfilter-on').change(DupPro.Pack.ToggleDBFilters);
    DupPro.Pack.ToggleDBFilters();
});
</script>
