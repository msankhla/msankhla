<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MagentoCloud\Test\Functional\Acceptance;

/**
 * This test runs on the latest version of PHP
 * @group php74
 */
class AdminCredential74Cest extends AdminCredentialCest
{
    /**
     * @var string
     */
    protected $magentoCloudTemplate = '2.4.3';
}
