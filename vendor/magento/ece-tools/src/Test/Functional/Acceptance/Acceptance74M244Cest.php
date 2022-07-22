<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MagentoCloud\Test\Functional\Acceptance;

use CliTester;
use Robo\Exception\TaskException;
use Codeception\Example;
use Magento\CloudDocker\Test\Functional\Codeception\Docker;
use Magento\MagentoCloud\Util\ArrayManager;

/**
 * @inheritDoc
 *
 * @group php74
 */
class Acceptance74M244Cest extends AcceptanceCest
{
    /**
     * @var string
     */
    protected $magentoCloudTemplate = '2.4.4';
}
