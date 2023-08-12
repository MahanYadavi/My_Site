<?php

/**
 * Schedule page controller
 *
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

namespace Duplicator\Controllers;

use DUP_PRO_Handler;
use Duplicator\Core\Controllers\ControllersManager;
use Duplicator\Core\Controllers\AbstractMenuPageController;

class SchedulePageController extends AbstractMenuPageController
{
    /**
     * tabs menu
     */
    const L2_SLUG_MAIN_SCHEDULES = 'schedules';
    /**
     * Class constructor
     */
    protected function __construct()
    {
        $this->parentSlug   = ControllersManager::MAIN_MENU_SLUG;
        $this->pageSlug     = ControllersManager::SCHEDULES_SUBMENU_SLUG;
        $this->pageTitle    = __('Schedules', 'duplicator-pro');
        $this->menuLabel    = __('Schedules', 'duplicator-pro');
        $this->capatibility = self::getDefaultCapadibily();
        $this->menuPos      = 30;

        add_action('duplicator_render_page_content_' . $this->pageSlug, array($this, 'renderContent'));
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
        DUP_PRO_Handler::init_error_handler();
        $inner_page = isset($_REQUEST['inner_page']) ? sanitize_text_field($_REQUEST['inner_page']) : 'schedules';

        switch ($inner_page) {
            case 'schedules':
                include(DUPLICATOR____PATH . '/views/schedules/schedule.list.php');
                break;
            case 'edit':
                include(DUPLICATOR____PATH . '/views/schedules/schedule.edit.php');
                break;
        }
    }
}
