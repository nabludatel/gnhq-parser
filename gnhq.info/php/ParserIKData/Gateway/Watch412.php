<?php
class ParserIKData_Gateway_Watch412 extends ParserIKData_Gateway_Abstract
{
    private $_table = 'watch_412';

    public function getCount($watchType, $okrugAbbr = null, $withDiscrepancy = false)
    {
        $conds = array();
        $conds[] = 'WatchType = "'.$this->_escapeString($watchType).'"';
        if ($okrugAbbr) {
            $conds[] = 'uik in (' . $this->_getUikGateway()->getCondOkrug($okrugAbbr) . ')';
        }
        if ($withDiscrepancy) {
            $conds[] = 'uik in (' .$this->_getProtocolGateway()->getCondDiscrepancy($watchType, null, null) . ')';
        }
        $cond = '(' . implode(') AND (', $conds) . ')';
        $result = $this->_getDriver()->query('SELECT COUNT(*) FROM '. $this->_table . ' WHERE ' . $cond);
        while ($res = $this->_fetchResultToArray($result)) {
            return ($res[0]);
        }
    }

    /**
     * @param string $watchType
     * @return string
     */
    public function getCondIn($watchType)
    {
        return 'SELECT uik FROM ' . $this->_table . ' WHERE WatchType = "'.$this->_escapeString($watchType).'"';
    }

    /**
    * @param string $watchType
    * @return string
    */
    public function getCondClear($watchType)
    {
        return 'SELECT uik FROM ' . $this->_table . ' WHERE WatchType = "'.$this->_escapeString($watchType).'" AND code = 1';
    }

    private function _getProtocolGateway()
    {
        return new ParserIKData_Gateway_Protocol412();
    }

    private function _getUikGateway()
    {
        return new ParserIKData_Gateway_UIK();
    }
}