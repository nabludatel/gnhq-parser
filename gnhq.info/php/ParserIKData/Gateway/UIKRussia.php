<?php
class ParserIKData_Gateway_UIKRussia extends ParserIKData_Gateway_Abstract
{
    private $_table = 'uik_russia';
    private $_modelClass = 'ParserIKData_Model_UIKRussia';

    /**
     * @return null|int
     */
    protected function _getCacheLifetime()
    {
        return 86400;
    }

    public function removeAll()
    {
        $this->_getDriver()->truncateTable($this->_table);
    }

    /**
     * @param ParserIKData_Model_UIKRussia $uikR
     */
    public function save($uikR)
    {
        $this->_getDriver()->query($this->_insertQuery($uikR));
    }

    public function getAll()
    {
        return $this->_loadFromTable($this->_table, $this->_modelClass);
    }

    /**
     * @param int $regionNum
     * @param int $uikNum
     * @return ParserIKData_Model_UIKRussia|NULL
     */
    public function getForRegionAndNum($regionNum, $uikNum)
    {
        $where = $this->_getCondRegionNum($regionNum) . ' AND ' . $this->_getCondShortUikNum($uikNum);
        $data = $this->_loadFromTable($this->_table, $this->_modelClass, $where);
        if (count($data) == 1) {
            return $data[0];
        } else {
            return null;
        }
    }


    /**
     * @param int|null $regionNum
     * @param string|null $okrugAbbr
     * @param int[] $uikNums
     * @return int
     */
    public function getCount($regionNum = null, $okrugAbbr = null, $uikNums = null)
    {
        $args = func_get_args();
        if (false === ($uikCount = $this->_loadFromCache(__CLASS__, __FUNCTION__, $args)) ) {
            $whereParts = array();
            if ($regionNum) {
                $whereParts[] = $this->_getCondRegionNum($regionNum);
            }
            if ($okrugAbbr) {
                $whereParts[] = $this->_getCondOkrug($okrugAbbr);
            }
            if ($uikNums) {
                $whereParts[] = $this->_getCondUikNum($uikNums);
            }
            if (empty ($whereParts)) {
                $whereParts[] = '(1 = 1)';
            }
            $where = implode(' AND ', $whereParts);
            $data = $this->_getDriver()->selectAssoc('Count(*) as CNT', $this->_table, $where);
            $uikCount =  $data[0]['CNT'];

            $this->_saveToCache(__CLASS__, __FUNCTION__, $args, $uikCount);
            //echo 'not from cache;';
        } else {
            //echo 'from cache';
        }

        return $uikCount;
    }

    /**
     * @param string $okrug
     * @return string
     */
    private function _getCondOkrug($okrug)
    {
        return sprintf('(FullName IN (SELECT uik FROM uik_okrug WHERE okrug = "%s"))', $this->_escapeString($okrug));
    }


    /**
     * по полному номеру УИК
     * @param unknown_type $uikNums
     * @return string
     */
    private function _getCondUikNum($uikNums)
    {
        if (!is_array($uikNums)) {
            $uikNums = array($uikNums);
        }
        foreach ($uikNums as $i => $num) {
            $uikNums[$i] = intval($num);
        }
        return '(FullName IN ('.implode(',', $uikNums).'))';
    }

    /**
    * по короткому номеру УИК (внутри региона)
    * @param int[] $uikNums
    * @return string
    */
    private function _getCondShortUikNum($uikNums)
    {
        if (!is_array($uikNums)) {
            $uikNums = array($uikNums);
        }
        foreach ($uikNums as $i => $num) {
            $uikNums[$i] = intval($num);
        }
        return '(UikNum IN ('.implode(',', $uikNums).'))';
    }

    /**
     * @param int $regionNum
     * @return string
     */
    private function _getCondRegionNum($regionNum)
    {
        return sprintf('(RegionNum = %d)', $regionNum);
    }


    /**
     * @param ParserIKData_Model_UIKRussia $uikR
     * @return string
     */
    private function _insertQuery($uikR)
    {
        $data = $uikR->toArray();
        $data = $this->_getDriver()->escapeArray($data);
        return sprintf('insert into '.$this->_table.'
        		(RegionNum, TikNum,  UikNum, FullName, Link, Place, VotingPlace, BorderDescription)
          values (%d, %d, %d, %d, "%s", "%s", "%s", "%s")',
        $data[0], $data[1], $data[2], $data[3], $data[4], $data[5], $data[6], $data[7]);
    }
}