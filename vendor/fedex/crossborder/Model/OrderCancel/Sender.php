<?php
/**
 * FedEx Cross Border component
 *
 * @category    FedEx
 * @package     FedEx_CrossBorder
 * @author      Andrey Rapshtynsky <andrey.bh@gmail.com>
 * @copyright   FedEx (https://crossborder.fedex.com/)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace FedEx\CrossBorder\Model\OrderCancel;

use FedEx\CrossBorder\Model\AbstractImport;

class Sender extends AbstractImport
{
    const ENDPOINT_PATH = 'order_cancel_path';
    const LOG_FILE      = 'FedEx/CrossBorder/OrderCancel.log';
}