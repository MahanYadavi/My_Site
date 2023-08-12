<?php

/**
 * Settings page controller
 *
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

namespace Duplicator\Controllers;

use Duplicator\Core\Controllers\ControllersManager;
use Duplicator\Core\Controllers\AbstractMenuPageController;
use Duplicator\Core\Controllers\PageAction;
use Duplicator\Core\Controllers\SubMenuItem;
use Duplicator\Core\Views\TplMng;
use Duplicator\Libs\Snap\SnapUtil;

class SettingsPageController extends AbstractMenuPageController
{
    const NONCE_ACTION = 'duppro-settings-package';

    /**
     * tabs menu
     */
    const L2_SLUG_GENERAL  = 'general';
    const L2_SLUG_PACKAGE  = 'package';
    const L2_SLUG_SCHEDULE = 'schedule';
    const L2_SLUG_STORAGE  = 'storage';
    const L2_SLUG_IMPORT   = 'import';

    /**
     * settings
     */
    const L3_SLUG_GENERAL_SETTINGS       = 'gensettings';
    const L3_SLUG_GENERAL_BETA_FEATHURES = 'bfeathures';
    const L3_SLUG_GENERAL_FEATHURES      = 'profile';
    const L3_SLUG_GENERAL_MIGRATE        = 'migrate';

    /**
     * package
     */
    const L3_SLUG_PACKAGE_BASIC    = 'basic';
    const L3_SLUG_PACKAGE_ADVANCED = 'advanced';
    const L3_SLUG_PACKAGE_BRAND    = 'brand';

    /**
     * storage
     */
    const L3_SLUG_STORAGE_GENERAL  = 'storage-general';
    const L3_SLUG_STORAGE_SSL      = 'ssl';
    const L3_SLUG_STORAGE_STORAGES = 'storage-types';

    /**
     * Class constructor
     */
    protected function __construct()
    {
        $this->parentSlug   = ControllersManager::MAIN_MENU_SLUG;
        $this->pageSlug     = ControllersManager::SETTINGS_SUBMENU_SLUG;
        $this->pageTitle    = __('Settings', 'duplicator-pro');
        $this->menuLabel    = __('Settings', 'duplicator-pro');
        $this->capatibility = self::getDefaultCapadibily();
        $this->menuPos      = 60;

        add_filter('duplicator_sub_menu_items_' . $this->pageSlug, array($this, 'getBasicSubMenus'));
        add_filter('duplicator_sub_level_default_tab_' . $this->pageSlug, array($this, 'getSubMenuDefaults'), 10, 2);
        add_action('duplicator_render_page_content_' . $this->pageSlug, array($this, 'renderContent'));
        add_filter('duplicator_page_actions_' . $this->pageSlug, array($this, 'pageActions'));
    }

    /**
     * Return sub menus for current page
     *
     * @param SubMenuItem[] $subMenus sub menus list
     *
     * @return SubMenuItem[]
     */
    public function getBasicSubMenus($subMenus)
    {
        $subMenus[] = new SubMenuItem(self::L2_SLUG_GENERAL, __('General', 'duplicator-pro'));
        $subMenus[] = new SubMenuItem(self::L2_SLUG_PACKAGE, __('Packages', 'duplicator-pro'));
        $subMenus[] = new SubMenuItem(self::L2_SLUG_SCHEDULE, __('Schedules', 'duplicator-pro'));
        $subMenus[] = new SubMenuItem(self::L2_SLUG_STORAGE, __('Storage', 'duplicator-pro'));
        $subMenus[] = new SubMenuItem(self::L2_SLUG_IMPORT, __('Import', 'duplicator-pro'));

        $subMenus[] = new SubMenuItem(self::L3_SLUG_GENERAL_SETTINGS, __('General Settings', 'duplicator-pro'), self::L2_SLUG_GENERAL);
        $subMenus[] = new SubMenuItem(self::L3_SLUG_GENERAL_MIGRATE, __('Migrate Settings', 'duplicator-pro'), self::L2_SLUG_GENERAL);
        //$subMenus[] = new SubMenuItem(self::L3_SLUG_GENERAL_BETA_FEATHURES, __('Beta Features', 'duplicator-pro'), self::L2_SLUG_GENERAL);
        $subMenus[] = new SubMenuItem(self::L3_SLUG_GENERAL_FEATHURES, __('New Features', 'duplicator-pro'), self::L2_SLUG_GENERAL);

        $subMenus[] = new SubMenuItem(self::L3_SLUG_PACKAGE_BASIC, __('Basic Settings', 'duplicator-pro'), self::L2_SLUG_PACKAGE);
        $subMenus[] = new SubMenuItem(self::L3_SLUG_PACKAGE_ADVANCED, __('Advanced Settings', 'duplicator-pro'), self::L2_SLUG_PACKAGE);
        $subMenus[] = new SubMenuItem(self::L3_SLUG_PACKAGE_BRAND, __('Installer Branding', 'duplicator-pro'), self::L2_SLUG_PACKAGE);

        $subMenus[] = new SubMenuItem(self::L3_SLUG_STORAGE_GENERAL, __('General', 'duplicator-pro'), self::L2_SLUG_STORAGE);
        $subMenus[] = new SubMenuItem(self::L3_SLUG_STORAGE_SSL, __('SSL', 'duplicator-pro'), self::L2_SLUG_STORAGE);
        $subMenus[] = new SubMenuItem(self::L3_SLUG_STORAGE_STORAGES, __('Storage Types', 'duplicator-pro'), self::L2_SLUG_STORAGE);

        return $subMenus;
    }

    /**
     * Return slug default for parent menu slug
     *
     * @param string $slug   current default
     * @param string $parent parent for default
     *
     * @return string default slug
     */
    public function getSubMenuDefaults($slug, $parent)
    {
        switch ($parent) {
            case '':
                return self::L2_SLUG_GENERAL;
            case self::L2_SLUG_GENERAL:
                return self::L3_SLUG_GENERAL_SETTINGS;
            case self::L2_SLUG_PACKAGE:
                return self::L3_SLUG_PACKAGE_BASIC;
            case self::L2_SLUG_STORAGE:
                return self::L3_SLUG_STORAGE_GENERAL;
            default:
                return $slug;
        }
    }

    /**
     * Return actions for current page
     *
     * @param PageAction[] $actions actions lists
     *
     * @return PageAction[]
     */
    public function pageActions($actions)
    {
        $actions[] = new PageAction(
            'save',
            array($this, 'saveBetaFeathure'),
            array($this->pageSlug,
                self::L2_SLUG_GENERAL,
                self::L3_SLUG_GENERAL_BETA_FEATHURES
            )
        );
        return $actions;
    }

    /**
     * Render page content
     *
     * @param string[] $currentLevelSlugs current page menu levels slugs
     *
     * @return void
     */
    public function renderContent($currentLevelSlugs)
    {
        require(DUPLICATOR____PATH . '/ctrls/ctrl.storage.setting.php');

        switch ($currentLevelSlugs[1]) {
            case self::L2_SLUG_GENERAL:
                $this->renderGeneral($currentLevelSlugs);
                break;
            case self::L2_SLUG_PACKAGE:
                $this->renderPackage($currentLevelSlugs);
                break;
            case self::L2_SLUG_IMPORT:
                include DUPLICATOR____PATH . '/views/settings/import.php';
                break;
            case self::L2_SLUG_SCHEDULE:
                include DUPLICATOR____PATH . '/views/settings/schedule.php';
                break;
            case self::L2_SLUG_STORAGE:
                \DUP_PRO_CTRL_Storage_Setting::controller();
                break;
        }
    }

    /**
     * Save beta feathure action
     *
     * @return array<string, string>
     */
    public function saveBetaFeathure()
    {
        $global = \DUP_PRO_Global_Entity::getInstance();
        $result = array();

        // $global->exampleFlag = filter_input(INPUT_POST, 'FIELD NAME', FILTER_VALIDATE_BOOLEAN) */

        if ($global->save() == false) {
            throw new \Exception('Can\'t update settings');
        } else {
            $result['successMessage'] = __('Settings updated.', 'duplicator-pro');
            $global->adjust_settings_for_system();
        }

        return $result;
    }

    /**
     * Render general sub tab
     *
     * @param string[] $currentLevelSlugs current page menu levels slugs
     *
     * @return void
     */
    protected function renderGeneral($currentLevelSlugs)
    {
        switch ($currentLevelSlugs[2]) {
            case self::L3_SLUG_GENERAL_SETTINGS:
                require DUPLICATOR____PATH . '/views/settings/general/inc.general.php';
                break;
            case self::L3_SLUG_GENERAL_BETA_FEATHURES:
                TplMng::getInstance()->render('admin_pages/settings/general/beta_features');
                break;
            case self::L3_SLUG_GENERAL_FEATHURES:
                $displayExperimental = false;
                require DUPLICATOR____PATH . '/views/settings/general/inc.feature.php';
                break;
            case self::L3_SLUG_GENERAL_MIGRATE:
                require DUPLICATOR____PATH . '/views/settings/general/inc.migrate.php';
                break;
        }
    }

    /**
     * Render package sub tab
     *
     * @param string[] $currentLevelSlugs current page menu levels slugs
     *
     * @return void
     */
    protected function renderPackage($currentLevelSlugs)
    {
        require DUPLICATOR____PATH . '/views/settings/package/main.php';
        switch ($currentLevelSlugs[2]) {
            case self::L3_SLUG_PACKAGE_BASIC:
                require DUPLICATOR____PATH . '/views/settings/package/inc.basic.php';
                break;
            case self::L3_SLUG_PACKAGE_ADVANCED:
                require DUPLICATOR____PATH . '/views/settings/package/inc.advanced.php';
                break;
            case self::L3_SLUG_PACKAGE_BRAND:
                $view = isset($_REQUEST['view']) ? SnapUtil::sanitize($_REQUEST['view']) : 'list';
                if ($view == 'list') {
                    require DUPLICATOR____PATH . '/views/settings/package/inc.brand.list.php';
                } else {
                    require DUPLICATOR____PATH . '/views/settings/package/inc.brand.edit.php';
                }
                break;
        }
    }
}
