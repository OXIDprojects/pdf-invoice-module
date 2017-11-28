<?php
/**
 * This file is part of OXID eSales Invoice PDF module.
 *
 * OXID eSales Invoice PDF module is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * OXID eSales Invoice PDF module is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OXID eSales Invoice PDF module.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @category      module
 * @package       oeinvoicepdf
 * @author        OXID eSales AG
 * @link          http://www.oxid-esales.com
 * @copyright (C) OXID eSales AG 2003-2016
 */

namespace Unit\Modules\Oe\Invoicepdf\Controllers\Admin;

use OxidEsales\Eshop\Core\Field;
use \InvoicepdfOrder_Overview;
use \oxTestModules;

/**
 * Tests for Order_Overview class
 */
class InvoicepdfOrderOverviewTest extends \OxidEsales\TestingLibrary\UnitTestCase
{
    /**
     * Prepares test suite.
     */
    protected function setUp()
    {
        parent::setUp();

        $invoicePdfOrderClass = getShopBasePath() . 'modules/oe/invoicepdf/controllers/admin/invoicepdforder_overview.php';
        if (!class_exists('InvoicepdfOrder_Overview', false)) {
            class_alias(\OxidEsales\Eshop\Application\Controller\Admin\OrderOverview::class, 'InvoicepdfOrder_Overview_parent');
            require_once $invoicePdfOrderClass;
        }
    }

    /**
     * Tear down the fixture.
     */
    protected function tearDown()
    {
        $this->cleanUpTable('oxorder');
        $this->cleanUpTable("oxorderarticles");
        parent::tearDown();
    }

    /**
     * Order_Overview::CanExport() test case
     *
     * @return null
     */
    public function testCanExport()
    {
        oxTestModules::addFunction('oxModule', 'isActive', '{ return true; }');

        $oBase = oxNew('oxbase');
        $oBase->init("oxorderarticles");
        $oBase->setId("_testOrderArticleId");
        $oBase->oxorderarticles__oxorderid = new Field("testOrderId");
        $oBase->oxorderarticles__oxamount = new Field(1);
        $oBase->oxorderarticles__oxartid = new Field("1126");
        $oBase->oxorderarticles__oxordershopid = new Field($this->getConfig()->getShopId());
        $oBase->save();

        $oView = $this->getMock(\OxidEsales\Eshop\Application\Controller\Admin\OrderOverview::class, array("getEditObjectId"));
        $oView->expects($this->any())->method('getEditObjectId')->will($this->returnValue('testOrderId'));

        $this->assertTrue($oView->canExport());
    }

    /**
     * Order_Overview::CreatePDF() test case
     */
    public function testCreatePDF()
    {
        $soxId = '_testOrderId';

        // writing test order
        $oOrder = oxNew(\OxidEsales\Eshop\Application\Model\Order::class);
        $oOrder->setId($soxId);
        $oOrder->oxorder__oxshopid        = new Field($this->getConfig()->getBaseShopId());
        $oOrder->oxorder__oxuserid        = new Field("oxdefaultadmin");
        $oOrder->oxorder__oxbillcompany   = new Field("Ihr Firmenname");
        $oOrder->oxorder__oxbillemail     = new Field(oxADMIN_LOGIN);
        $oOrder->oxorder__oxbillfname     = new Field("Hans");
        $oOrder->oxorder__oxbilllname     = new Field("Mustermann");
        $oOrder->oxorder__oxbillstreet    = new Field("Musterstr");
        $oOrder->oxorder__oxbillstreetnr  = new Field("10");
        $oOrder->oxorder__oxbillcity      = new Field("Musterstadt");
        $oOrder->oxorder__oxbillcountryid = new Field("a7c40f6320aeb2ec2.72885259");
        $oOrder->oxorder__oxbillzip       = new Field("79098");
        $oOrder->oxorder__oxbillsal       = new Field("Herr");
        $oOrder->oxorder__oxpaymentid     = new Field("1f53d82f6391b86db09786fd75b69cb9");
        $oOrder->oxorder__oxpaymenttype   = new Field("oxidcashondel");
        $oOrder->oxorder__oxtotalnetsum   = new Field(75.55);
        $oOrder->oxorder__oxtotalbrutsum  = new Field(89.9);
        $oOrder->oxorder__oxtotalordersum = new Field(117.4);
        $oOrder->oxorder__oxdelcost       = new Field(20);
        $oOrder->oxorder__oxdelval        = new Field(0);
        $oOrder->oxorder__oxpaycost       = new Field(7.5);
        $oOrder->oxorder__oxcurrency      = new Field("EUR");
        $oOrder->oxorder__oxcurrate       = new Field(1);
        $oOrder->oxorder__oxdeltype       = new Field("oxidstandard");
        $oOrder->oxorder__oxordernr       = new Field(1);
        $oOrder->save();
        $this->setRequestParameter("oxid", $soxId);
        oxTestModules::addFunction('oxUtils', 'setHeader', '{ if ( !isset( $this->_aHeaderData ) ) { $this->_aHeaderData = array();} $this->_aHeaderData[] = $aA[0]; }');
        oxTestModules::addFunction('oxUtils', 'getHeaders', '{ return $this->_aHeaderData; }');
        oxTestModules::addFunction('oxUtils', 'showMessageAndExit', '{ $this->_aHeaderData[] = "testExportData"; }');

        // testing..
        $oView = new InvoicepdfOrder_Overview();
        $oView->createPDF();

        $aHeaders = \OxidEsales\Eshop\Core\Registry::getUtils()->getHeaders();
        $this->assertEquals("Pragma: public", $aHeaders[0]);
        $this->assertEquals("Cache-Control: must-revalidate, post-check=0, pre-check=0", $aHeaders[1]);
        $this->assertEquals("Expires: 0", $aHeaders[2]);
        $this->assertEquals("Content-type: application/pdf", $aHeaders[3]);
        $this->assertEquals("Content-Disposition: attachment; filename=1_Mustermann.pdf", $aHeaders[4]);
        $this->assertEquals("testExportData", $aHeaders[5]);
    }
}
