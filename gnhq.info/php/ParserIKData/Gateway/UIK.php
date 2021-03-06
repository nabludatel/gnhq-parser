<?php
class ParserIKData_Gateway_UIK extends ParserIKData_Gateway_Abstract
{
    private $_table = 'uik';
    private $_view_uik_okrug = 'uik_okrug';

    /**
     * @param string $okrugAbbr
     * @param string|null $watchType
     * @param boolean $oProto
     * @param boolean $oClean
     * @param boolean $oDiscrep
     * @param boolean $oReport
     * @return ParserIKData_Model_UIK[]
     */
    public function getForOkrug($okrugAbbr, $watchType = null, $oProto = false, $oClean = false, $oDiscrep = false, $oReport = false)
    {
        $args = func_get_args();

        if (false === ($uiks = $this->_loadFromCache(get_called_class(), __FUNCTION__, $args)) ) {
            $conds = array();
            $conds[] =  'FullName IN (' . $this->_getCondOkrug($okrugAbbr) . ')';
            if ($watchType) {
                $conds[] = 'FullName IN ( '. $this->_getCondWatchType($watchType). ')';
            }
            if ($oProto) {
                $conds[] = 'FullName IN ('.$this->_getProtocolGateway()->getCondResultType($watchType).')';

                if ($oClean) {
                    $conds[] = 'FullName IN (' . $this->_getWatchGateway()->getCondClear($watchType) . ')';
                } elseif ($oDiscrep) {
                    $conds[] = 'FullName IN (' . $this->_getProtocolGateway()->getCondDiscrepancy($watchType) .')';
                }
            }
            if ($oReport) {
                $conds[] = 'FullName IN (' . $this->_getReportGateway()->getCondWithReport($watchType) . ')';
            }

            $cond = '( ' . implode(' ) AND (', $conds) . ' )';
            $result = $this->_getDriver()->select('*', $this->_table, $cond, null, null);
            $uiks = array();
            while ( ($data = $this->_fetchResultToArray($result)) !== false) {
                $uik = ParserIKData_Model_UIK::fromArray($data);
                $uiks[$uik->getUniqueId()] = $uik;
            }

            $this->_saveToCache(get_called_class(), __METHOD__, $args, $uiks);
        }
        return $uiks;
    }

    public function getCondOkrug($okrugAbbr)
    {
        return $this->_getCondOkrug($okrugAbbr);
    }

    private function _getCondOkrug($okrugAbbr)
    {
        $tikOkrugCond = $this->_getTikGateway()->getCondOkrug($okrugAbbr);
        return 'SELECT uik FROM ' . $this->_view_uik_okrug . ' WHERE okrug = "'. $this->_getDriver()->escapeString($okrugAbbr) .'"';
    }

    private function _getCondWatchType($watchType)
    {
        return $this->_getWatchGateway()->getCondIn($watchType);
    }
}