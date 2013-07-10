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

require_once PY_SOAP . 'Client/PlentymarketsSoapClient.php';
require_once PY_COMPONENTS . 'Config/PlentymarketsConfig.php';
require_once PY_COMPONENTS . 'Mapping/PlentymarketsMappingController.php';
require_once PY_COMPONENTS . 'Utils/PlentymarketsUtils.php';
require_once PY_COMPONENTS . 'Import/PlentymarketsImportController.php';
require_once PY_COMPONENTS . 'Export/PlentymarketsExportController.php';

/**
 *
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
class PlentymarketsCronjobController
{

	/**
	 *
	 * @var integer
	 */
	CONST INTERVAL_IMPORT_ITEM = 3600;

	/**
	 *
	 * @var integer
	 */
	CONST INTERVAL_IMPORT_ITEM_PRICE = 3600;

	/**
	 *
	 * @var integer
	 */
	CONST INTERVAL_IMPORT_ITEM_STOCK = 900;

	/**
	 *
	 * @var integer
	 */
	CONST INTERVAL_IMPORT_ORDER = 3600;

	/**
	 *
	 * @var integer
	 */
	CONST INTERVAL_EXPORT = 300;

	/**
	 *
	 * @var integer
	 */
	CONST INTERVAL_EXPORT_ORDER = 900;

	/**
	 *
	 * @var integer
	 */
	CONST INTERVAL_EXPORT_ORDER_INCOMING_PAYMENT = 1800;

	/**
	 *
	 * @var PlentymarketsCronjobController
	 */
	protected static $Instance;

	/**
	 *
	 * @var boolean
	 */
	protected $mayRun = true;

	/**
	 *
	 * @var PlentymarketsConfig
	 */
	protected $Config;

	/**
	 * Checks whether any cronjob may run
	 */
	protected function __construct()
	{
		// Check whether any cronjob my be executed due to api status
		$this->mayRun = PlentymarketsUtils::checkDxStatus()
						&& PlentymarketsConfig::getInstance()->getMayDatexActual(0);

		$this->Config = PlentymarketsConfig::getInstance();
	}

	/**
	 *
	 * @return PlentymarketsCronjobController
	 */
	public static function getInstance()
	{
		if (!self::$Instance instanceof self)
		{
			self::$Instance = new self();
		}
		return self::$Instance;
	}

	/**
	 * Order Export
	 *
	 * @param Shopware_Components_Cron_CronJob $Job
	 */
	public function runOrderExport(Shopware_Components_Cron_CronJob $Job)
	{
		// Check whether the timelimit is undershot
		if ($Job->getJob()->getEnd()->getTimestamp() + 600 > time())
		{
			return;
		}

		$this->Config->setExportOrderLastRunTimestamp(time());
		$this->Config->setExportOrderNextRunTimestamp(time() + $Job->getJob()->getInterval());

		if (!$this->mayRun)
		{
			$this->Config->setExportOrderStatus(0);
			return;
		}

		try
		{
			PlentymarketsExportController::getInstance()->exportOrders();
			$this->Config->setExportOrderStatus(1);
			$this->Config->setExportOrderError('');
		}
		catch (Exception $E)
		{
			$this->Config->setExportOrderStatus(2);
			$this->Config->setExportOrderError($E->getMessage());
		}
	}

	/**
	 * Order Incoming Item Export
	 *
	 * @param Shopware_Components_Cron_CronJob $Job
	 */
	public function runOrderIncomingPaymentExport(Shopware_Components_Cron_CronJob $Job)
	{
		$this->Config->setExportOrderIncomingPaymentLastRunTimestamp(time());
		$this->Config->setExportOrderIncomingPaymentNextRunTimestamp(time() + $Job->getJob()->getInterval());

		if (!$this->mayRun)
		{
			$this->Config->setExportOrderIncomingPaymentStatus(0);
			return;
		}

		try
		{
			PlentymarketsExportController::getInstance()->exportIncomingPayments();
			$this->Config->setExportOrderIncomingPaymentStatus(1);
			$this->Config->setExportOrderIncomingPaymentError('');
		}
		catch (Exception $E)
		{
			$this->Config->setExportOrderIncomingPaymentStatus(2);
			$this->Config->setExportOrderIncomingPaymentError($E->getMessage());
		}
	}

	/**
	 * Order Import
	 *
	 * @param Shopware_Components_Cron_CronJob $Job
	 */
	public function runOrderImport(Shopware_Components_Cron_CronJob $Job)
	{
		$this->Config->setImportOrderLastRunTimestamp(time());
		$this->Config->setImportOrderNextRunTimestamp(time() + $Job->getJob()->getInterval());

		if (!$this->mayRun)
		{
			$this->Config->setImportOrderStatus(0);
			return;
		}

		try
		{
			PlentymarketsImportController::importOrders();
			$this->Config->setImportOrderStatus(1);
			$this->Config->setImportOrderError('');
		}
		catch (Exception $E)
		{
			$this->Config->setImportOrderStatus(2);
			$this->Config->setImportOrderError($E->getMessage());
		}
	}

	/**
	 * Export
	 *
	 * @param Shopware_Components_Cron_CronJob $Job
	 */
	public function runExport(Shopware_Components_Cron_CronJob $Job)
	{
		try
		{
			PlentymarketsExportController::getInstance()->export();
		}
		catch (Exception $E)
		{
			PlentymarketsLogger::getInstance()->error('Cron:Export', $E->getMessage());
		}
	}

	/**
	 * Item Import
	 *
	 * @param Shopware_Components_Cron_CronJob $Job
	 */
	public function runItemImport(Shopware_Components_Cron_CronJob $Job)
	{
		$this->Config->setImportItemLastRunTimestamp(time());
		$this->Config->setImportItemNextRunTimestamp(time() + $Job->getJob()->getInterval());

		if (!$this->mayRun)
		{
			$this->Config->setImportItemStatus(0);
			return;
		}

		try
		{
			PlentymarketsImportController::getItemsBase();
			$this->Config->setImportItemStatus(1);
			$this->Config->setImportItemError('');
		}
		catch (Exception $E)
		{
			$this->Config->setImportItemStatus(2);
			$this->Config->setImportItemError($E->getMessage());
		}
	}

	/**
	 * Item Price Import
	 *
	 * @param Shopware_Components_Cron_CronJob $Job
	 */
	public function runItemPriceImport(Shopware_Components_Cron_CronJob $Job)
	{
		$this->Config->setImportItemPriceLastRunTimestamp(time());
		$this->Config->setImportItemPriceNextRunTimestamp(time() + $Job->getJob()->getInterval());

		if (!$this->mayRun)
		{
			$this->Config->setImportItemPriceStatus(0);
			return;
		}

		try
		{
			PlentymarketsImportController::importItemPrices();
			$this->Config->setImportItemPriceStatus(1);
			$this->Config->setImportItemPriceError('');
		}
		catch (Exception $E)
		{
			$this->Config->setImportItemPriceStatus(2);
			$this->Config->setImportItemPriceError($E->getMessage());
		}
	}

	/**
	 * Item Stock Import
	 *
	 * @param Shopware_Components_Cron_CronJob $Job
	 */
	public function runItemStockImport(Shopware_Components_Cron_CronJob $Job)
	{
		$this->Config->setImportItemStockLastRunTimestamp(time());
		$this->Config->setImportItemStockNextRunTimestamp(time() + $Job->getJob()->getInterval());

		if (!$this->mayRun)
		{
			$this->Config->setImportItemStockStatus(0);
			return;
		}

		try
		{
			PlentymarketsImportController::importItemStocks();
			$this->Config->setImportItemStockStatus(1);
			$this->Config->setImportItemStockError('');
		}
		catch (Exception $E)
		{
			$this->Config->setImportItemStockStatus(2);
			$this->Config->setImportItemStockError($E->getMessage());
		}
	}
}
