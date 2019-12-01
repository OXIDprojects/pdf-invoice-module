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

/**
 * Class InvoicepdfOrder_Overview extends order_overview.
 */
class InvoicepdfOrder_Overview extends InvoicepdfOrder_Overview_parent
{

    /**
     * Add Languages to parameters.
     *
     * @return string
     */
    public function render()
    {
        $return = parent::render();

        $oLang = \OxidEsales\Eshop\Core\Registry::getLang();
        $this->_aViewData["alangs"] = $oLang->getLanguageNames();

        return $return;
    }

    /**
     * Returns pdf export state - can export or not
     *
     * @deprecated since v5.3 (2016-08-06); logic of this method will be moved to the InvoicePDF module.
     *
     * @return bool
     */
    public function canExport()
    {
        // We force reading from master to prevent issues with slow replications or open transactions (see ESDEV-3804).
        $masterDb = \OxidEsales\Eshop\Core\DatabaseProvider::getMaster();
        $sOrderId = $this->getEditObjectId();

        $viewNameGenerator = \OxidEsales\Eshop\Core\Registry::get(\OxidEsales\Eshop\Core\TableViewNameGenerator::class);
        $sTable = $viewNameGenerator->getViewName("oxorderarticles");

        $sQ = "select count(oxid) from {$sTable} where oxorderid = " . $masterDb->quote($sOrderId) . " and oxstorno = 0";
        $blCan = (bool) $masterDb->getOne($sQ);

        return $blCan;
    }

    /**
     * Performs PDF export to user (outputs file to save).
     */
    public function createPDF()
    {
        $soxId = $this->getEditObjectId();
        if ($soxId != "-1" && isset($soxId)) {
            // load object
            $oOrder = oxNew(\OxidEsales\Eshop\Application\Model\Order::class);
            if ($oOrder->load($soxId)) {
                $oUtils = \OxidEsales\Eshop\Core\Registry::getUtils();
                $sTrimmedBillName = trim($oOrder->oxorder__oxbilllname->getRawValue());
                $sFilename = ($oOrder->oxorder__oxordernr->value . "_" . $sTrimmedBillName . ".pdf");
                $sFilename = $this->makeValidFileName($sFilename);
                ob_start();
                $oOrder->genPDF($sFilename, \OxidEsales\Eshop\Core\Registry::getConfig()->getRequestParameter("pdflanguage"));
                $sPDF = ob_get_contents();
                ob_end_clean();
                $oUtils->setHeader("Pragma: public");
                $oUtils->setHeader("Cache-Control: must-revalidate, post-check=0, pre-check=0");
                $oUtils->setHeader("Expires: 0");
                $oUtils->setHeader("Content-type: application/pdf");
                $oUtils->setHeader("Content-Disposition: attachment; filename=" . $sFilename);
                \OxidEsales\Eshop\Core\Registry::getUtils()->showMessageAndExit($sPDF);
            }
        }
    }
}
