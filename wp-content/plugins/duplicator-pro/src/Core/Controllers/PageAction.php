<?php

/**
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

namespace Duplicator\Core\Controllers;

use Duplicator\Libs\Snap\SnapLog;
use Duplicator\Libs\Snap\SnapUtil;

/**
 * Action page class
 */
class PageAction
{
    /** @var string */
    protected $key;
    /** @var callable */
    protected $callback;
    /** @var string[] */
    protected $menuSlugs = array();
    /** @var string */
    protected $innerPage = '';

    /**
     * Class constructor
     *
     * @param string   $key       action key
     * @param callable $callback  action callback
     * @param string[] $menuSlugs page where the action is active
     * @param string   $innerPage current inner page if defined
     */
    public function __construct($key, $callback, $menuSlugs = array(), $innerPage = '')
    {
        if (strlen($key) == 0) {
            throw new \Exception('action key can\'t be empty');
        }

        if (!is_callable($callback)) {
            throw new \Exception('action callback have to be callable function');
        }

        $this->key       = $key;
        $this->callback  = $callback;
        $this->menuSlugs = $menuSlugs;
        $this->innerPage = $innerPage;
    }

    /**
     *
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * Return action nonce key
     *
     * @return string
     */
    public function getNonceKey()
    {
        $result = 'dup_nonce_';
        foreach ($this->menuSlugs as $slug) {
            $result .= $slug . '_';
        }

        return str_replace(array('-', '.', '\\', '/'), '_', $result . $this->key);
    }

    /**
     * Creates a cryptographic token tied to a specific action, user, user session,
     * and window of time.
     *
     * @return string The token.
     */
    public function getNonce()
    {
        return wp_create_nonce($this->getNonceKey());
    }

    /**
     * Get input hidden element with nonce action field
     *
     * @param bool $echo if true echo nonce field else return string
     *
     * @return string
     */
    public function getActionNonceFileds($echo = true)
    {
        ob_start();
        wp_nonce_field($this->getNonceKey());
        echo '<input type="hidden" class="dup-action-form-action" name="' . ControllersManager::QUERY_STRING_MENU_KEY_ACTION . '" value="' . $this->key . '" >';
        if ($echo) {
            ob_end_flush();
            return '';
        } else {
            return ob_get_clean();
        }
    }

    /**
     * Return true if current page is the page of current action
     *
     * @param string[] $currentMenuSlugs Current page menu levels slugs
     *
     * @return boolean
     */
    public function isPageOfCurrentAction($currentMenuSlugs)
    {
        foreach ($this->menuSlugs as $index => $slug) {
            if (!isset($currentMenuSlugs[$index]) || $currentMenuSlugs[$index] != $slug) {
                return false;
            }
        }

        return true;
    }

    /**
     * Return true if current action is called
     *
     * @param string[] $currentMenuSlugs Current page menu levels slugs
     * @param string   $currentInnerPage Current inner page
     * @param string   $action           Action to check
     *
     * @return boolean
     */
    public function isCurrentAction($currentMenuSlugs, $currentInnerPage, $action)
    {
        if ($action !== $this->key) {
            return false;
        }

        foreach ($this->menuSlugs as $index => $slug) {
            if (!isset($currentMenuSlugs[$index]) || $currentMenuSlugs[$index] != $slug) {
                return false;
            }
        }

        if (strlen($this->innerPage) > 0 && $this->innerPage !== $currentInnerPage) {
            return false;
        }

        return true;
    }

    /**
     * Verify action nonce
     *
     * @see wp_verify_nonce wordpress function
     *
     * @return int|false 1 if the nonce is valid and generated between 0-12 hours ago,
     *                   2 if the nonce is valid and generated between 12-24 hours ago.
     *                   False if the nonce is invalid.
     */
    protected function verifyNonce()
    {
        $nonce = SnapUtil::filterInputDefaultSanitizeString(SnapUtil::INPUT_REQUEST, '_wpnonce', false);
        return wp_verify_nonce($nonce, $this->getNonceKey());
    }

    /**
     * Exect callback action
     *
     * @param array<string, mixed> $resultData generic allaray where put addtional action data
     *
     * @return bool
     */
    public function exec(&$resultData = array())
    {
        $result = true;
        try {
            if (!$this->verifyNonce()) {
                throw new \Exception('Security issue on action ' . $this->key);
            }
            $funcResultData = call_user_func($this->callback);
            $resultData     = array_merge($resultData, $funcResultData);
        } catch (\Exception $e) {
            $resultData['actionsError']  = true;
            $resultData['errorMessage'] .= '<b>' . $e->getMessage() . '</b><pre>' . SnapLog::getTextException($e, false) . '</pre>';
            $result                      = false;
        } catch (\Error $e) {
            $resultData['actionsError']  = true;
            $resultData['errorMessage'] .= '<b>' . $e->getMessage() . '</b><pre>' . SnapLog::getTextException($e, false) . '</pre>';
            $result                      = false;
        }
        return $result;
    }
}
