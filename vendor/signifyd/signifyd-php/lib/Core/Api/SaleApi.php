<?php
/**
 * CaseApi for the Signifyd SDK
 *
 * PHP version 5.6
 *
 * @category  Signifyd_Fraud_Protection
 * @package   Signifyd\Core
 * @author    Signifyd <info@signifyd.com>
 * @copyright 2018 SIGNIFYD Inc. All rights reserved.
 * @license   See LICENSE.txt for license details.
 * @link      https://www.signifyd.com/
 */
namespace Signifyd\Core\Api;

use Signifyd\Core\Connection;
use Signifyd\Core\Exceptions\ApiException;
use Signifyd\Core\Exceptions\InvalidClassException;
use Signifyd\Models\SaleModel;

/**
 * Class CaseApi
 *
 * @category Signifyd_Fraud_Protection
 * @package  Signifyd\Core
 * @author   Signifyd <info@signifyd.com>
 * @license  See LICENSE.txt for license details.
 * @link     https://www.signifyd.com/
 */
class SaleApi extends ApiModel
{
    /**
     * CaseApi constructor.
     *
     * @param array $args The settings values
     *
     * @throws \Signifyd\Core\Exceptions\LoggerException
     * @throws \Signifyd\Core\Exceptions\ConnectionException
     */
    public function __construct($args = [])
    {
        parent::__construct($args);
        $this->logger->info('SaleApi initialized');
    }

    /**
     * Create a case in Signifyd
     *
     * @param \Signifyd\Models\CaseModel $order The case data
     *
     * @return bool|\Signifyd\Core\Response\SaleResponse
     *
     * @throws ApiException
     * @throws InvalidClassException
     * @throws \Signifyd\Core\Exceptions\LoggerException
     */
    public function createOrder($endpoint, $order)
    {
        $this->logger->info('SaleApi: CreateOrder method called');
        if (is_array($order)) {
            $order = new SaleModel($order);
            $valid = $order->validate();
            if (true !== $valid) {
                $this->logger->error(
                    'Order not valid after array init: ' . json_encode($valid)
                );
            }
        } elseif ($order instanceof SaleModel) {
            $valid = $order->validate();
            if (true !== $valid) {
                $this->logger->error(
                    'Order not valid after object init: ' . json_encode($valid)
                );
            }
        } else {
            $this->logger->error('Invalid parameter for create order');
            throw new ApiException(
                'Invalid parameter for create order'
            );
        }

        $this->logger->info(
            'Connection call sale api with: ' . $order->toJson()
        );
        $response = $this->connection->callApi(
            $endpoint,
            $order->toJson(),
            'post',
            'sale'
        );

        return $response;
    }
}