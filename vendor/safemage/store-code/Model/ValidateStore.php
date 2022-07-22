<?php
/*
NOTICE OF LICENSE

This source file is subject to the SafeMageEULA that is bundled with this package in the file LICENSE.txt.

It is also available at this URL: https://www.safemage.com/LICENSE_EULA.txt

Copyright (c)  SafeMage (https://www.safemage.com/)
*/

namespace SafeMage\StoreCode\Model;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Model\StoreIsInactiveException;

/**
 * Validate that store is active by its code.
 */
class ValidateStore
{
    /**
     * @var StoreRepositoryInterface
     */
    private $storeRepository;

    /**
     * @param StoreRepositoryInterface $storeRepository
     */
    public function __construct(StoreRepositoryInterface $storeRepository)
    {
        $this->storeRepository = $storeRepository;
    }

    /**
     * Validate that store is active by its code.
     *
     * @param string $storeCode
     * @return array
     */
    public function validate($storeCode)
    {
        try {
            $store = $this->storeRepository->getActiveStoreByCode($storeCode);
            $result = ['store' => $store];
        } catch (StoreIsInactiveException $e) {
            $result = ['error' => __('Requested store is inactive')];
        } catch (NoSuchEntityException $e) {
            $result = ['error' => __('Requested store is not found')];
        }

        return $result;
    }
}
