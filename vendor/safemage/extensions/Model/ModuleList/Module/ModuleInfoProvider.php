<?php
/*
NOTICE OF LICENSE

This source file is subject to the SafeMageEULA that is bundled with this package in the file LICENSE.txt.

It is also available at this URL: https://www.safemage.com/LICENSE_EULA.txt

Copyright (c)  SafeMage (https://www.safemage.com/)
*/


namespace SafeMage\Extensions\Model\ModuleList\Module;

/**
 * Class provides information about SafeMage modules.
 */
class ModuleInfoProvider
{
    /**
     * @var array
     */
    private $moduleList = [];

    /**
     * @var array
     */
    private $moduleConfigList = [];

    /**
     * @var \SafeMage\Extensions\Model\ModuleList\Loader
     */
    private $loader;

    /**
     * @var string|null
     */
    private $cacheKey = null;

    /**
     * @param \SafeMage\Extensions\Model\ModuleList\Loader $loader
     */
    public function __construct(
        \SafeMage\Extensions\Model\ModuleList\Loader $loader
    ) {
        $this->loader = $loader;
    }

    /**
     * Form extensions list array for output.
     *
     * @return array
     */
    public function getModulesList()
    {
        if (empty($this->moduleList)) {
            $moduleList = [];
            foreach ($this->getModuleConfigList() as $moduleCode => $moduleConfig) {
                $moduleList[$moduleCode] = [
                    'name'    => isset($moduleConfig['module_name']) ? $moduleConfig['module_name'] : $moduleCode,
                    'version' => isset($moduleConfig['setup_version']) ? $moduleConfig['setup_version'] : '',
                    'url' => isset($moduleConfig['url']) ? $moduleConfig['url'] : '',
                    'cache_key' => isset($moduleConfig['cache_key']) ? $moduleConfig['cache_key'] : '',
                ];
            }
            $this->moduleList = $moduleList;
        }
        return $this->moduleList;
    }

    /**
     * Retrieve list of SafeMage modules.
     *
     * @return array
     */
    private function getModuleConfigList()
    {
        if (empty($this->moduleConfigList)) {
            $moduleConfigList = $this->loader->load();
            $moduleList = [];

            foreach ($moduleConfigList as $code => $config) {
                if (!$this->canShowExtension($code, $config)) {
                    continue;
                }
                $moduleList[$code] = $config;
            }
            $this->moduleConfigList = $moduleList;
        }
        return $this->moduleConfigList;
    }

    /**
     * Verify if extension can be shown.
     *
     * @param string $code
     * @param array $config
     * @return bool
     */
    private function canShowExtension($code, array $config)
    {
        if (!$code || !$config) {
            return false;
        }
        if ($this->isProtectedExtension($code)) {
            return false;
        }
        return true;
    }

    /**
     * Check if current module is supposed to be shown.
     *
     * @param string $code
     * @return bool
     */
    private function isProtectedExtension($code)
    {
        return $code == 'SafeMage_Extensions';
    }

    /**
     * Get extension cache key.
     *
     * @return null|string
     */
    public function getCacheKey()
    {
        if (is_null($this->cacheKey)) {
            foreach ($this->getModuleConfigList() as $config) {
                $this->cacheKey .= isset($config['cache_key']) ? $config['cache_key'] : '';
            }
        }
        return $this->cacheKey;
    }
}
