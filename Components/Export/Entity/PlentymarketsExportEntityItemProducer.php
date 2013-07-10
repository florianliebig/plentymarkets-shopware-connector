<?php
/**
 * plentymarkets shopware connector
 * Copyright © 2013 plentymarkets GmbH
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License, supplemented by an additional
 * permission, and of our proprietary license can be found
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "plentymarkets" is a registered trademark of plentymarkets GmbH.
 * "shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, titles and interests in the
 * above trademarks remain entirely with the trademark owners.
 *
 * @copyright  Copyright (c) 2013, plentymarkets GmbH (http://www.plentymarkets.com)
 * @author     Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */

require_once PY_SOAP . 'Models/PlentySoapObject/Producer.php';
require_once PY_SOAP . 'Models/PlentySoapRequest/SetProducers.php';
require_once PY_SOAP . 'Models/PlentySoapObject/GetProducers.php';

/**
 *
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
class PlentymarketsExportEntityItemProducer
{

	/**
	 *
	 * @var array
	 */
	protected $PLENTY_name2ID = array();

	/**
	 * Build the index of existing data
	 */
	protected function buildPlentyNameIndex()
	{
		$Response_GetProducers = PlentymarketsSoapClient::getInstance()->GetProducers();

		foreach ($Response_GetProducers->Producers->item as $Producer)
		{
			$this->PLENTY_name2ID[$Producer->ProducerName] = $Producer->ProducerID;
		}
	}

	/**
	 * Build the index and start the export
	 */
	public function export()
	{
		// Index first
		$this->buildPlentyNameIndex();
		$this->doExport();
	}

	/**
	 * Export the missind producers
	 */
	protected function doExport()
	{

		$producerNameMappingShopware = array();
		$supplierRepository = Shopware()->Models()->getRepository('Shopware\Models\Article\Supplier');

		$Request_SetProducers = new PlentySoapRequest_SetProducers();
		foreach ($supplierRepository->findAll() as $Supplier)
		{
			$Supplier instanceof Shopware\Models\Article\Supplier;
			$Object_SetProducer = new PlentySoapObject_Producer();

			if (array_key_exists($Supplier->getName(), $this->PLENTY_name2ID))
			{
				PlentymarketsMappingController::addProducer($Supplier->getId(), $this->PLENTY_name2ID[$Supplier->getName()]);
			}
			else
			{
				$Object_SetProducer->ProducerExternalName = $Supplier->getName();
				$Object_SetProducer->ProducerName = $Supplier->getName();
				$Object_SetProducer->ProducerHomepage = $Supplier->getLink();
				$Request_SetProducers->Producers[] = $Object_SetProducer;
				$producerNameMappingShopware[$Supplier->getName()] = $Supplier->getId();
			}
		}

		if (count($Request_SetProducers->Producers))
		{
			$Response_SetProducers = PlentymarketsSoapClient::getInstance()->SetProducers($Request_SetProducers);
			foreach ($Response_SetProducers->ResponseMessages->item as $ResponseMessage)
			{
				PlentymarketsMappingController::addProducer(
					$producerNameMappingShopware[$ResponseMessage->IdentificationValue],
					$ResponseMessage->SuccessMessages->item[0]->Value
				);
			}
		}
	}
}
