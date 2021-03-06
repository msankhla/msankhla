<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CloudDocker\Test\Functional\Acceptance;

use CliTester;
use Codeception\Example;
use Robo\Exception\TaskException;

/**
 * @group php74
 */
class Elasticsearch74Cest extends ElasticsearchCest
{
    /**
     * Template version for testing
     */
    protected const TEMPLATE_VERSION = '2.4.0';

    /**
     * @return array
     */
    protected function dataProvider(): array
    {
        return [
            [
                'version' => '6.5',
                'xms' => '518m',
                'xmx' => '518m',
                'param' => [
                    'key' => 'node.store.allow_mmapfs',
                    'value' => 'false',
                    'needle' => '"store":{"allow_mmapfs":"false"}',
                ]
            ],
            [
                'version' => '7.5',
                'xms' => '520m',
                'xmx' => '520m',
                'plugins' => ['analysis-nori'],
                'param' => [
                    'key' => 'node.store.allow_mmap',
                    'value' => 'false',
                    'needle' => '"store":{"allow_mmap":"false"}',
                ]
            ],
        ];
    }
}
