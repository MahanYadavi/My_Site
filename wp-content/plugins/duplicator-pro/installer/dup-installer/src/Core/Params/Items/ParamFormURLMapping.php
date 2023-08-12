<?php

/**
 * @package   Duplicator\Installer
 * @copyright (c) 2022, Snap Creek LLC
 */

namespace Duplicator\Installer\Core\Params\Items;

use Duplicator\Installer\Core\Params\Models\SiteOwrMap;
use Duplicator\Installer\Core\Params\PrmMng;
use DUPX_ArchiveConfig;
use Exception;

/**
 * this class handles the entire block selection block.
 */
class ParamFormURLMapping extends ParamFormSitesOwrMap
{
    const FORM_TYPE_URL_MAPPING = 'url_mapping';

    protected $currentSubsiteId = -1;

    /**
     * Class constructor
     *
     * @param string $name     param identifier
     * @param string $type     TYPE_STRING | TYPE_ARRAY_STRING | ...
     * @param string $formType FORM_TYPE_HIDDEN | FORM_TYPE_TEXT | ...
     * @param array  $attr     list of attributes
     * @param array  $formAttr list of form attributes
     */
    public function __construct($name, $type, $formType, $attr = null, $formAttr = array())
    {
        if ($type != self::TYPE_ARRAY_SITES_OWR_MAP) {
            throw new Exception('the type must be ' . self::TYPE_ARRAY_SITES_OWR_MAP);
        }

        if ($formType != self::FORM_TYPE_URL_MAPPING) {
            throw new Exception('the form type must be ' . self::FORM_TYPE_URL_MAPPING);
        }

        ParamForm::__construct($name, $type, $formType, $attr, $formAttr);
        $this->minListItems = 0;
    }

    /**
     * Render HTML
     *
     * @return void
     */
    protected function htmlItem()
    {
        if ($this->formType == self::FORM_TYPE_URL_MAPPING) {
            $this->sitesOrwHtml();
        } else {
            parent::htmlItem();
        }
    }

    /**
     * Get add itm button label
     *
     * @param string $key label key
     *
     * @return string
     */
    protected function getItemsLabels($key)
    {
        $paramLabels = [
            'addItem' => 'Add custom URL',
            'sourceSite' => 'Source Site',
            'targetSite' => 'Custom URL'
        ];

        return (isset($paramLabels[$key]) ? $paramLabels[$key] : 'unknown label key');
    }

    /**
     * Return target data
     *
     * @return mixed[]
     */
    protected function getTargetData()
    {
        $result                = [];
        $result['isSubdomain'] = (DUPX_ArchiveConfig::getInstance()->mu_mode === 1);
        $result['mainSiteUrl'] = PrmMng::getInstance()->getValue(PrmMng::PARAM_URL_NEW);
        $result['subsites']    = [];
        return $result;
    }

    /**
     * Get subsites list in packages
     *
     * @return ParamOption[]
     */
    public static function getSourceIdsOptions()
    {
        $mainSiteId = DUPX_ArchiveConfig::getInstance()->main_site_id;
        $options    = parent::getSourceIdsOptions();
        foreach ($options as $index => $opt) {
            if ($opt->value == $mainSiteId) {
                unset($options[$index]);
                break;
            }
        }
        return array_values($options);
    }

    /**
     * Get existing subsites list on import site
     *
     * @return ParamOption[]
     */
    public static function getTargetIdsOptions()
    {
        static $targetOpt = null;

        if (is_null($targetOpt)) {
            $targetOpt[] = new ParamOption(
                SiteOwrMap::NEW_SUBSITE_WITH_SLUG,
                'New ' . (DUPX_ArchiveConfig::getInstance()->mu_mode == 1 ? 'Domain' : 'Path'),
                ParamOption::OPT_ENABLED
            );

            $targetOpt[] = new ParamOption(
                SiteOwrMap::NEW_SUBSITE_WITH_FULL_DOMAIN,
                'New URL',
                ParamOption::OPT_ENABLED
            );
        }

        return $targetOpt;
    }
}
