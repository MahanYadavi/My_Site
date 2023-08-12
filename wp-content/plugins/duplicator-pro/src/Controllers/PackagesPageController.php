<?php

/**
 * Packages page page controller
 *
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

namespace Duplicator\Controllers;

use DUP_PRO_Global_Entity;
use Duplicator\Core\Controllers\ControllersManager;
use Duplicator\Core\Controllers\AbstractMenuPageController;
use Exception;

class PackagesPageController extends AbstractMenuPageController
{
    const L2_SLUG_PACKAGE_BUILD  = 'packages';
    const L2_SLUG_PACKAGE_DETAIL = 'detail';

    /**
     * Class constructor
     */
    protected function __construct()
    {
        $this->parentSlug   = ControllersManager::MAIN_MENU_SLUG;
        $this->pageSlug     = ControllersManager::PACKAGES_SUBMENU_SLUG;
        $this->pageTitle    = __('Packages', 'duplicator-pro');
        $this->menuLabel    = __('Packages', 'duplicator-pro');
        $this->capatibility = self::getDefaultCapadibily();
        $this->menuPos      = 10;

        add_action('duplicator_render_page_content_' . $this->pageSlug, array($this, 'renderContent'));
        add_filter('duplicator_page_template_data_' . $this->pageSlug, array($this, 'updatePackagePageTitle'));
        add_filter('set_screen_option_package_screen_options', array('DUP_PRO_Package_Screen', 'set_screen_options'), 11, 3);
    }

    /**
     * Set package page title
     *
     * @param array<string, mixed> $tplData template global data
     *
     * @return array<string, mixed>
     */
    public function updatePackagePageTitle($tplData)
    {

        $_REQUEST['action'] = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'main';
        switch ($_REQUEST['action']) {
            case 'detail':
                $title = $this->getPackageDetailTitle();
                break;
            default:
                $title = $this->getPackageListTitle();
                break;
        }
        $tplData['pageTitle'] = $title;
        return $tplData;
    }

    /**
     * Return create package link
     *
     * @return string
     */
    public function getPackageBuildUrl()
    {
        return $this->getMenuLink(
            self::L2_SLUG_PACKAGE_BUILD,
            null,
            array(
                'inner_page' => 'new1',
                '_wpnonce' => wp_create_nonce('new1-package')
            )
        );
    }

    /**
     * called on admin_print_styles-[page] hook
     *
     * @return void
     */
    public function pageStyles()
    {
        wp_enqueue_style('dup-pro-packages');
    }

    /**
     * Show gift
     *
     * @return bool
     */
    public static function showGift()
    {
        $global = DUP_PRO_Global_Entity::getInstance();
        return (DUPLICATOR_PRO_GIFT_THIS_RELEASE && $global->dupHidePackagesGiftFeatures); // @phpstan-ignore-line
    }

    /**
     * Get package detail title page
     *
     * @return string
     */
    protected function getPackageDetailTitle()
    {
        $package_id = isset($_REQUEST["id"]) ? sanitize_text_field($_REQUEST["id"]) : 0;
        $package    = \DUP_PRO_Package::get_by_id($package_id);
        if (!is_object($package)) {
            return __('Package Details » Not Found');
        } else {
            return sprintf(__('Package Details » %1$s', 'duplicator-pro'), $package->Name);
        }
    }

    /**
     * Get package list title page
     *
     * @return string
     */
    protected function getPackageListTitle()
    {
        $inner_page = isset($_REQUEST['inner_page']) ? sanitize_text_field($_REQUEST['inner_page']) : 'list';
        $postfix    = '';
        switch ($inner_page) {
            case 'list':
                $postfix = __('All', 'duplicator-pro');
                break;
            case 'new1':
                $postfix = __('New', 'duplicator-pro');
                break;
            case 'new2':
                $postfix = __('New', 'duplicator-pro');
                break;
            default:
                throw new Exception('Invalid inner page');
        }
        return __('Packages', 'duplicator-pro') . " » " . $postfix;
    }

    /**
     * Render page content
     *
     * @param array<string, string> $currentLevelSlugs current menu slugs
     *
     * @return void
     */
    public function renderContent($currentLevelSlugs)
    {
        require(DUPLICATOR____PATH . '/views/packages/controller.php');
    }

    /**
     * Get package detail url
     *
     * @param int $package_id package id
     *
     * @return string
     */
    public function getPackageDetailsUrl($package_id)
    {
        return $this->getMenuLink(
            self::L2_SLUG_PACKAGE_DETAIL,
            null,
            array(
                'action' => 'detail',
                'id' => $package_id,
                '_wpnonce' => wp_create_nonce('package-detail')
            )
        );

/*
        var link = '?page=duplicator-pro&action=detail&tab=detail&id=' + id + '&_wpnonce=' + '<?php echo wp_create_nonce('package-detail'); ?>';
        if (newWindow) {
            window.open(link,"_blank")
        }
        window.location.href = link;*/
    }
}
