<?php
/*
NOTICE OF LICENSE

This source file is subject to the SafeMageEULA that is bundled with this package in the file LICENSE.txt.

It is also available at this URL: https://www.safemage.com/LICENSE_EULA.txt

Copyright (c)  SafeMage (https://www.safemage.com/)
*/

namespace SafeMage\Extensions\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Enable/disable SafeMage modules.
 */
class ManageModules implements ObserverInterface
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * @var \Magento\Framework\Module\Status
     */
    private $moduleStatus;

    /**
     * @var \Magento\Framework\App\Cache\TypeListInterface
     */
    private $cacheTypeList;

    /**
     * @var \Magento\Framework\App\State\CleanupFiles
     */
    private $cleanupFiles;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    private $eventManager;

    /**
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\Module\Status $moduleStatus
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Magento\Framework\App\State\CleanupFiles $cleanupFiles
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Module\Status $moduleStatus,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\App\State\CleanupFiles $cleanupFiles,
        \Magento\Framework\Event\ManagerInterface $eventManager
    ) {
        $this->request = $request;
        $this->moduleStatus = $moduleStatus;
        $this->cacheTypeList = $cacheTypeList;
        $this->cleanupFiles = $cleanupFiles;
        $this->eventManager = $eventManager;
    }

    /**
     * Enable or disables SafeMage modules.
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $groups = $this->request->getParam('groups');

        if (!isset($groups['extension']['fields']['enabled'])) {
            return $this;
        }
        $extensions = $groups['extension']['fields']['enabled'];
        if (count($extensions) == 0) {
            return $this;
        }

        $enableExtensions = [];
        $disableExtensions = [];
        foreach ($extensions as $key => $value) {
            if (!$key || $key == 'value') {
                continue;
            }

            if ($value) {
                $enableExtensions[] = $key;
            } else {
                $disableExtensions[] = $key;
            }
        }

        if ($enableExtensions) {
            if ($enableExtensions = $this->moduleStatus->getModulesToChange(true, $enableExtensions)) {
                $this->moduleStatus->setIsEnabled(true, $enableExtensions);
            }
        }

        if ($disableExtensions) {
            if ($disableExtensions = $this->moduleStatus->getModulesToChange(false, $disableExtensions)) {
                $this->moduleStatus->setIsEnabled(false, $disableExtensions);
            }
        }

        if ($enableExtensions || $disableExtensions) {
            $cacheTypes = $this->cacheTypeList->getTypes();
            foreach($cacheTypes as $cacheType => $data) {
                $this->cacheTypeList->cleanType($cacheType);
            }

            $this->cleanupFiles->clearMaterializedViewFiles();
            $this->eventManager->dispatch('clean_static_files_cache_after');
        }

        return $this;
    }
}
