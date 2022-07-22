<?php

namespace Narvar\Accord\Plugin;

use Narvar\Accord\Helper\CustomLogger;
use Narvar\Accord\Helper\Processor;
use Narvar\Accord\Helper\Util;
use Narvar\Accord\Helper\NoFlakeLogger;

class ShipmentBase
{

    private $logger;

    private $processor;

    private $util;

    private $noFlakeLogger;

    public function __construct(
        CustomLogger $logger,
        Processor $processor,
        Util $util,
        NoFlakeLogger $noFlakeLogger
    ) {
        $this->logger            = $logger;
        $this->processor         = $processor;
        $this->util              = $util;
        $this->noFlakeLogger     = $noFlakeLogger;
    }

    public function afterSave($shipment)
    {
        $orderId    = $shipment->getOrder()->getIncrementId();
        $storeId    = $shipment->getOrder()->getStoreId();
        $eventName  = "narvar_shipment_plugin";
        try {
            $this->util->logMetadata($orderId, $storeId, $eventName, 'start');
            $retailerMoniker = $this->util->getRetailerMoniker($storeId);
            if (!empty($retailerMoniker)) {
                $narvarShipmentObject = $this->util->getNarvarOrderObject(
                    $shipment->getOrder(),
                    $eventName
                );
                $narvarShipmentObject['shipment'] = $this->util->getShipmentData($shipment, $storeId);
                $this->processor->sendPluginData($narvarShipmentObject, $retailerMoniker, $eventName);
                $this->noFlakeLogger->logNoFlakeData(
                    $narvarShipmentObject,
                    $eventName,
                    $retailerMoniker
                );
            } else {
                $this->util->logMetadata(
                    $orderId,
                    $storeId,
                    $eventName,
                    'end - Narvar Accord Not Configured for this Store Id'
                );
            }
        } catch (\Exception $ex) {
            $this->util->handleException($ex, $orderId, $storeId, $eventName);
        } finally {
            return $shipment;
        }
    }
}
