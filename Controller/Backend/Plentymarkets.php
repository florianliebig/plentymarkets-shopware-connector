<?php

use PlentyConnector\Connector\ConfigService\ConfigServiceInterface;
use PlentyConnector\Connector\Mapping\MappingServiceInterface;
use PlentyConnector\Connector\QueryBus\Query\Manufacturer\GetManufacturerQuery;
use PlentyConnector\Connector\TransferObject\MappedTransferObjectInterface;
use PlentyConnector\Connector\TransferObject\Mapping\MappingInterface;
use PlentymarketsAdapter\Client\ClientInterface;
use Shopware\Components\Api\Manager;

/**
 * Class Shopware_Controller_Backend_Plentymarkets.
 */
class Shopware_Controllers_Backend_Plentymarkets extends Shopware_Controllers_Backend_ExtJs
{
    /**
     * @throws \Exception
     */
    public function testApiCredentialsAction()
    {
        /**
         * @var ClientInterface $client
         */
        $client = $this->container->get('plentmarkets_adapter.client');

        $params = [
            'username' => $this->Request()->get('ApiUsername'),
            'password' => $this->Request()->get('ApiPassword'),
        ];

        $options = [
            'base_url' => $this->Request()->get('ApiUrl'),
        ];

        $login = $client->request('POST', 'login', $params, null, null, $options);

        $success = false;

        if (isset($login['accessToken'])) {
            $success = true;
        }

        $this->View()->assign(array(
            'success' => $success,
        ));
    }

    /**
     * @throws \Exception
     */
    public function saveSettingsAction()
    {
        /**
         * @var ConfigServiceInterface $config
         */
        $config = $this->container->get('plentyconnector.config');

        $config->set('rest_url', $this->Request()->get('ApiUrl'));
        $config->set('rest_username', $this->Request()->get('ApiUsername'));
        $config->set('rest_password', $this->Request()->get('ApiPassword'));

        $this->View()->assign(array(
            'success' => true,
            'data' => $this->Request()->getParams(),
        ));
    }

    /**
     * @throws \Exception
     */
    public function getSettingsListAction()
    {
        $config = $this->container->get('plentyconnector.config');

        $this->View()->assign(array(
            'success' => true,
            'data' => [
                'ApiUrl' => $config->get('rest_url'),
                'ApiUsername' => $config->get('rest_username'),
                'ApiPassword' => $config->get('rest_password'),
            ],
        ));
    }

    /**
     * Loads stores settings.
     */
    public function getSettingsViewDataAction()
    {
        /**
         * @var Shopware\Components\Api\Resource\Manufacturer
         */
        $resource = Manager::getResource('manufacturer');
        $manufacturers = $resource->getList(0, null)['data'];

//        $queryBus = $this->container->get('plentyconnector.query_bus');
//
//        $warehouses = array_map(function(ResponseItem $item) {
//            return array(
//                'name' => $item->getItem()->getName()
//            );
//        }, $queryBus->handle(new GetRemoteWarehouseQuery()));
//
//        $orderReferrers = array_map(function(ResponseItem $item) {
//            return array(
//                'name' => $item->getItem()->getName()
//            );
//        }, $queryBus->handle(new GetRemoteOrderReferrerQuery()));

        $this->View()->assign(array(
            'success' => true,
            'data' => [
                'manufacturers' => $manufacturers,
                'warehouses' => [],
                'orderReferrers' => [],
            ],
        ));
    }

    /**
     * @throws \Exception
     */
    public function getMappingsAction()
    {
        /**
         * @var MappingServiceInterface $mappingService
         */
        $mappingService = Shopware()->Container()->get('plentyconnector.mapping_service');
        $mappingInformation = $mappingService->getMappingInformation();

        $transferObjectMapping = function (MappedTransferObjectInterface $object) {
            return [
                'identifier' => $object->getIdentifier(),
                'type' => $object::getType(),
                'name' => $object->getName()
            ];
        };

        $this->View()->assign([
            'success' => true,
            'data' => array_map(function (MappingInterface $mapping) use ($transferObjectMapping) {
                return [
                    'originAdapterName' => $mapping->getOriginAdapterName(),
                    'destinationAdapterName' => $mapping->getDestinationAdapterName(),
                    'originTransferObjects' => array_map($transferObjectMapping, $mapping->getOriginTransferObjects()),
                    'destinationTransferObjects' => array_map($transferObjectMapping,
                        $mapping->getDestinationTransferObjects()),
                ];
            }, $mappingInformation)
        ]);
    }

    /**
     *
     */
    public function updateIdentityAction()
    {

    }
}