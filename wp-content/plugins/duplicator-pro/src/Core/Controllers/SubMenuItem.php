<?php

namespace Duplicator\Core\Controllers;

/**
 * Sub menu item class
 */
class SubMenuItem
{
    /** @var string */
    public $slug = '';
    /** @var string */
    public $label = '';
    /** @var string */
    public $parent = '';
    /** @var bool|string */
    public $perms = '';
    /** @var int */
    public $position = 10;
    /** @var string */
    public $link = '';
    /** @var bool */
    public $active = false;

    /**
     * Class constructor
     *
     * @param string      $slug     item slug
     * @param string      $label    menu label
     * @param string      $parent   parent slug
     * @param bool|string $perms    item permissions, true if have pare permission
     * @param int         $position position
     */
    public function __construct($slug, $label = '', $parent = '', $perms = true, $position = 10)
    {
        $this->slug     = (string) $slug;
        $this->label    = (string) $label;
        $this->parent   = (string) $parent;
        $this->perms    = $perms;
        $this->position = $position;
    }
}
