<?php

/**
 * Debug menu page controller
 *
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

namespace Duplicator\Controllers;

use Duplicator\Core\Controllers\ControllersManager;
use Duplicator\Core\Controllers\AbstractMenuPageController;

class DebugPageController extends AbstractMenuPageController
{
    /**
     * Class constructor
     */
    protected function __construct()
    {
        $this->parentSlug   = ControllersManager::MAIN_MENU_SLUG;
        $this->pageSlug     = ControllersManager::DEBUG_SUBMENU_SLUG;
        $this->pageTitle    = __('Testing Interface', 'duplicator-pro');
        $this->menuLabel    = __('Debug', 'duplicator-pro');
        $this->capatibility = self::getDefaultCapadibily();
        $this->menuPos      = 40;

        add_action('duplicator_render_page_content_' . $this->pageSlug, array($this, 'renderContent'));
    }

    /**
     * Return true if current page is enabled
     *
     * @return boolean
     */
    public function isEnabled()
    {
        if (!is_admin()) {
            return false;
        }
        $global = \DUP_PRO_Global_Entity::getInstance();
        return $global->debug_on;
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
        require(DUPLICATOR____PATH . '/views/debug/main.php');
    }
}
