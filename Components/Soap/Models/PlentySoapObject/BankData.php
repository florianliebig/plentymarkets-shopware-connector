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


/**
 */
class PlentySoapObject_BankData
{
	
	/**
	 * @var int
	 */
	public $AccountNumber;
	
	/**
	 * @var string
	 */
	public $BIC;
	
	/**
	 * @var int
	 */
	public $BLZ;
	
	/**
	 * @var string
	 */
	public $BankCity;
	
	/**
	 * @var string
	 */
	public $BankName;
	
	/**
	 * @var string
	 */
	public $BankPLZ;
	
	/**
	 * @var string
	 */
	public $BankStreet;
	
	/**
	 * @var string
	 */
	public $IBAN;
	
	/**
	 * @var string
	 */
	public $OwnerFirstname;
	
	/**
	 * @var string
	 */
	public $OwnerLastname;
}