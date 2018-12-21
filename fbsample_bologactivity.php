<?php
/**
 * 2007-2018 Frédéric BENOIST
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 *  @author    Frédéric BENOIST
 *  @copyright 2013-2018 Frédéric BENOIST <https://www.fbenoist.com/>
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

if (!defined('_PS_VERSION_')) {
     exit;
}

class FbSample_BoLogActivity extends Module
{
    public function __construct()
    {
        $this->name = 'fbsample_bologactivity';
        $this->tab = 'administration';
        $this->version = '2.1.0';
        $this->author = 'Frédéric BENOIST';
        $this->need_instance = 0;
        $this->is_configurable = 0;
        $this->bootstrap = true;

        parent::__construct();
        $this->ps_versions_compliancy = array(
            'min' => '1.7.0',
            'max' => _PS_VERSION_
        );
        $this->displayName = $this->l('BO Log Activity');
        $this->description = $this->l('Log module install/uninstall and carrier update');
    }

    public function install()
    {
        if (!parent::install()
            || !$this->registerHook('actionCarrierUpdate')
            || !$this->registerHook('actionModuleInstallAfter')
            || !$this->registerHook('actionModuleRegisterHookAfter')
            || !$this->registerHook('actionModuleUnRegisterHookAfter')) {
                return false;
        }
        return true;
    }

    public function uninstall()
    {
        if (!$this->unregisterHook('actionModuleInstallAfter')
            || !$this->unregisterHook('actionModuleRegisterHookAfter')
            || !$this->unregisterHook('actionModuleUnRegisterHookAfter')
            || !parent::uninstall()) {
                return false;
        }
        return true;
    }

    private static function logObjectEvent($event, $object)
    {
        if (!Validate::isLoadedObject($object)) {
            return;
        }

        if (get_class($object) == get_class()) {
            return;
        }

        $log_message = sprintf('%s call %s', get_class($object), $event);
        PrestaShopLogger::addLog(
            $log_message,
            1,
            null,
            null,
            (int)$object->id,
            true
        );
    }

    public function hookactionCarrierUpdate($params)
    {
        self::logObjectEvent('Update carrier', $params['carrier']);
    }

    public function hookactionModuleInstallAfter($params)
    {
        self::logObjectEvent('Install module', $params['object']);
    }

    public function hookactionModuleRegisterHookAfter($params)
    {
        if (Validate::isHookName($params['hook_name'])) {
            self::logObjectEvent('Register Hook '.$params['hook_name'], $params['object']);
        }
    }

    public function hookactionModuleUnRegisterHookAfter($params)
    {
        if (Validate::isHookName($params['hook_name'])) {
            self::logObjectEvent('Unregister Hook '.$params['hook_name'], $params['object']);
        }
    }
}
