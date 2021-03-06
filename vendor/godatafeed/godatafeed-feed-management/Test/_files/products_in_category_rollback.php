<?php

/**
 * Copyright 2018 Method Merchant, LLC or its affiliates. All Rights Reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License").
 * You may not use this file except in compliance with the License.
 * A copy of the License is located at
 *
 *  http://aws.amazon.com/apache2.0
 *
 * or in the "license" file accompanying this file. This file is distributed
 * on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either
 * express or implied. See the License for the specific language governing
 * permissions and limitations under the License.
 */

use Magento\TestFramework\Helper\Bootstrap;

/** @var \Magento\Framework\Registry $registry */
$registry = Bootstrap::getObjectManager()->get(\Magento\Framework\Registry::class);

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

/** @var $category \Magento\Catalog\Model\Category */
$category = Bootstrap::getObjectManager()->create(\Magento\Catalog\Model\Category::class);
$category->load(333);
if ($category->getId()) {
    $category->delete();
}

/** @var $product \Magento\Catalog\Model\Product */
$product = Bootstrap::getObjectManager()->create(\Magento\Catalog\Model\Product::class);
$product->load(5000);
if ($product->getId()) {
    $product->delete();
}
/** @var $product \Magento\Catalog\Model\Product */
$product = Bootstrap::getObjectManager()->create(\Magento\Catalog\Model\Product::class);
$product->load(5001);
if ($product->getId()) {
    $product->delete();
}
/** @var $product \Magento\Catalog\Model\Product */
$product = Bootstrap::getObjectManager()->create(\Magento\Catalog\Model\Product::class);
$product->load(5002);
if ($product->getId()) {
    $product->delete();
}
/** @var $product \Magento\Catalog\Model\Product */
$product = Bootstrap::getObjectManager()->create(\Magento\Catalog\Model\Product::class);
$product->load(5003);
if ($product->getId()) {
    $product->delete();
}
