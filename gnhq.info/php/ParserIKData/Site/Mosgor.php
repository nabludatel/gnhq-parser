<?php
class ParserIKData_Site_Mosgor
{
    const SITE = 'http://mosgorizbirkom.ru/';
    //const PAGE_HIERARCHY = 'list-Inside-doc-WholePage.aspx?RgmFolderID=bbe8fc2b-08b9-4971-9c70-00409d8db963';
    const PAGE_HIERARCHY = 'list-Inside-doc-WholePage.aspx?RgmFolderID=0ca2051d-085f-4228-b283-af0b0b582c3c';
    const PAGE_TIK_H = 'list-Inside.aspx?RgmFolderID=2fb99465-0918-4abc-b722-78f40de82495';

    private $_debugCnt = 0;

    /**
     * @var ParserIKData_Parser
     */
    private $_parser = null;
    /**
     * @var ParserIKData_DataMiner
     */
    private $_miner = null;

    /**
     * @return ParserIKData_Parser
     */
    private function _getParser()
    {
        if ($this->_parser === null) {
            $this->_parser = new ParserIKData_Parser();
        }
        return $this->_parser;
    }
    /**
     * @return ParserIKData_DataMiner
     */
    private function _getMiner()
    {
        if ($this->_miner === null) {
            $this->_miner = new ParserIKData_DataMiner();
        }
        return $this->_miner;
    }

    /**
     * создание всех объектов по первой иерархии (страница Информация территориальных избирательных комиссий)
     * @return void
     */
    public function initModelsByHierarchy()
    {
        $loader = new ParseIKData_Loader(self::SITE . self::PAGE_HIERARCHY, true);
        $result = $loader->load();

        // find links
        $okrugString = 'административный округ';
        $this->_getParser()->setPageSource($result);
        $okrugTags = $this->_getParser()->findSurroundingTags($okrugString);
        $okrugLinks = $this->_getMiner()->getLinks($okrugTags);


        // tiks
        $tikString = 'Сведения об избирательных участках';
        foreach($okrugLinks as $okrugName => $okrugLink) {
            //print_r($okrugName .': ' . $okrugLink . PHP_EOL);
            $okrug = ParserIKData_Model_Okrug::createFromPageInfo($okrugName, $okrugLink, array());
            $src = self::SITE . $okrugLink;
            $loader->setSource($src);
            $tikResult = $loader->load();
            $this->_getParser()->setPageSource($tikResult);
            $tikTags = $this->_getParser()->findSurroundingTags($tikString);
            $tikLinks = $this->_getMiner()->getLinks($tikTags);
            // print_r($tikLinks);
            foreach ($tikLinks as $tikName => $tikLink) {
                $tikRealName = trim(str_replace($tikString, '', $tikName));
                $tikRealName = $this->_clearStringData($tikRealName, true);
                $tik = ParserIKData_Model_TIK::createFromPageInfo($tikRealName, $tikLink, array());
                $okrug->addTik($tik);
            }
        }
    }

    /**
     * @return void
     */
    public function loadTikDataForOkrugs()
    {
        $okrugString = 'административном округе';
        $src = self::SITE . 'list-Inside.aspx?RgmFolderID=2fb99465-0918-4abc-b722-78f40de82495';
        $loader = new ParseIKData_Loader($src, true);
        $result = $loader->load();
        $this->_getParser()->setPageSource($result);
        $okrugTags = $this->_getParser()->findSurroundingTags($okrugString);
        $okrugTikLinks = $this->_getMiner()->getLinks($okrugTags);

        foreach ($okrugTikLinks as $name => $link) {
            $okrug = ParserIKData_Model_Okrug::findByModifiedName($name, array('- В'));
            if ($okrug) {
                $okrug->setTikDataLink($link);
            }
        }
    }

    /**
     * @return void
     */
    public function loadTikAddressAndSostavLinks()
    {
        foreach (ParserIKData_Model_Okrug::getAllObjects() as $okrug) {

            /* loading tik links */
            $src = $okrug->getTikDataLink();
            $loader = new ParseIKData_Loader(self::SITE . $src, true);
            $result = $loader->load();
            $this->_getParser()->setPageSource($result);
            $tikTags = $this->_getParser()->findSurroundingTags('район');
            $tikTagLinks = $this->_getMiner()->getLinks($tikTags);

            foreach ($tikTagLinks as $tikName => $tikLink) {
                /* @var $tik ParserIKData_Model_TIK */
                $tik = ParserIKData_Model_TIK::findByModifiedName($tikName);
                if ($tik instanceof ParserIKData_Model_TIK) {
                    $tik->setSelfInfoLink($tikLink);
                }
            }
        }

        foreach (ParserIKData_Model_TIK::getAllOBjects() as $tik) {
            if ($tik->getSelfInfoLink() != '') {
                $loader = new ParseIKData_Loader(self::SITE . $tik->getSelfInfoLink(), true);
                $result = $loader->load();
                $this->_getParser()->setPageSource($result);

                $tik->setSostavLink($this->_findLinkForPhraze('Состав ТИК'));
                $tik->setAddressLink($this->_findLinkForPhraze('Адрес мест'));
            }
        }
    }



    public function loadTikData()
    {
        foreach (ParserIKData_Model_TIK::getAllOBjects() as $tik) {
            $this->_loadTikAddress($tik);
            $this->_loadTikSostav($tik);
        }
    }



    /**
     * @param ParserIKData_Model_TIK $tik
     */
    public function createTikUiks(ParserIKData_Model_TIK $tik)
    {
        $loader = new ParseIKData_Loader(self::SITE . html_entity_decode($tik->getLink()), true);
        $page = $loader->load();
        $forPrintString = 'Версия для печати';
        $this->_getParser()->setPageSource($page);
        $data = $this->_getParser()->findSurroundingTags($forPrintString);
        $links = $this->_getMiner()->getLinks($data);
        reset($links);
        $printVersionLink = current($links);
        $loader->setSource(self::SITE . $printVersionLink);
        $printVersionPage = $loader->load();

        $string = 'Описание границ';
        $table = $this->_getParser()->setPageSource($printVersionPage)->findMinContainingTag($string, 'table');
        $this->_createUiksByTable($table, $tik);

        // patch for Щукино - two tables on page
        if ($tik->getUniqueId() == ParserIKData_Model_TIK::findByModifiedName('Щукино')->getUniqueId()) {
            $table = $this->_getParser()->findMinContainingTag('2992', 'table');
            $this->_createUiksByTable($table, $tik);
        }
        print_r($tik->getFullName(). ' processed' . PHP_EOL);
    }

    /**
     * @param html $table
     * @param ParserIKData_Model_TIK $tik
     */
    private function _createUiksByTable($table, $tik)
    {
        if (!$table) {
            return;
        }
        $rows = $this->_getMiner()->extractRows($table, 100);
        foreach ($rows as $row) {
            $cells = $this->_getMiner()->extractCells($row, 100);
            $uik = $this->_createUikFromTableCells($cells, $tik);
        }
    }

    /**
     * @param string $cells[]
     * @param ParserIKData_Model_TIK @tik
     * @return ParserIKData_Model_UIK|null
     */
    private function _createUikFromTableCells($cells, $tik)
    {
        foreach ($cells as $i => $cell) {
            $cells[$i] = $this->_clearStringData($cell);
            if ($i == 0 && !is_numeric($cells[$i])) {
                return null;
            }
        }
        $existingUik = ParserIKData_Model_UIK::getFromPool($cells[0]);
        if (!$existingUik) {
            $uik = ParserIKData_Model_UIK::createFromPageInfo($cells[0], '', array());
            /* @var $uik ParserIKData_Model_UIK */
            $uik->setBorderDescription($cells[1])->setPlace($cells[2])->setVotingPlace($cells[3])->_friendSetTik($tik);
            $tik->addUik($uik);
            return $uik;
        } else {
            print_r($tik->getFullName() . ': ' .implode('|' ,$cells) . PHP_EOL . str_repeat('!', 20). PHP_EOL);
            return null;
        }
    }


    /**
     * @param ParserIKData_Model_TIK $tik
     * @return ParserIKData_Model_TIK
     */
    private function _loadTikAddress($tik)
    {
        $loader = new ParseIKData_Loader(self::SITE . $tik->getAddressLink(), true);
        $result = $loader->load();

        $address = $this->_clearStringData($this->_getParser()->stringInBetween($result, '<strong>Адрес:', '</p>', false));
        $pos = strpos($address, 'Тел:');
        if ($pos > 0) {
            $address = substr($address, 0, $pos);
        }

        $phone   = $this->_clearStringData($this->_getParser()->stringInBetween($result, 'Тел:</strong>', '</p>', false));
        $phone = str_replace(array('Просмотреть увеличенную карту', '>'), array('',''), $phone);

        return $tik->setAddress($address)->setPhone($phone);
    }

    /**
     * @param ParserIKData_Model_TIK $tik
     * @return ParserIKData_Model_TIK
     */
    private function _loadTikSostav($tik)
    {
        $loader = new ParseIKData_Loader(self::SITE . $tik->getSostavLink(), true);
        $result = $loader->load();

        $sostavHtml = $this->_getParser()->stringInBetween($result, '<td valign="top" class="default">', '</td>', false);
        $this->_parseAndLoadSostavData($sostavHtml, $tik);
        return $tik;
    }


    /**
     * @param string $phraze
     * @return string
     */
    private function _findLinkForPhraze($phraze)
    {
        $link = null;
        $tags = $this->_getParser()->findSurroundingTags($phraze);
        $links = $this->_getMiner()->getLinks($tags);
        reset($links);
        $link = current($links);
        return html_entity_decode($link);
    }

    /**
     * @param string $sostav
     * @param ParserIKData_Model_TIK $tik
     * @return ParserIKData_Model_TIK
     */
    private function _parseAndLoadSostavData($sostav, $tik)
    {
        $sostav = $this->_clearStringData($sostav, false);
        $sostavParts = explode('</div>', $sostav);
        $chief       = null;
        $deputy      = null;
        $secretary   = null;
        $members     = array();
        foreach ($sostavParts as $k => $part) {

            $part = str_replace('&ndash;', '', strip_tags($part));

            if (!trim($part)) {
                continue;
            }
            if (mb_stristr($part, 'Члены', null, mb_detect_encoding($part)) !== false) {
                continue;
            }
            if ($this->_getChief($part)) {
                $chief = $this->_getChief($part);
                $chief = str_replace(array('Председатель', 'комиссии', '-'), array('', '', ''), $chief);
                $chief = trim($chief);
                continue;
            }
            if ($this->_getDeputy($part)) {
                $deputy = $this->_getDeputy($part);
                $deputy = str_replace(array('Заместитель', 'председателя', 'комиссии', '-'), array('', '', '', ''), $deputy);
                $deputy = trim($deputy);
                continue;
            }
            if ($this->_getSecretary($part)) {
                $secretary = $this->_getSecretary($part);
                $secretary = str_replace(array('Секретарь', 'комиссии', '-'), array('', '', ''), $secretary);
                $secretary = trim($secretary);
                continue;
            }
            if (trim($part)) {
                $members[] = $part;
            }
        }

        $tik->setChief($chief)->setDeputy($deputy)->setSecretary($secretary)->setMembers($members);
        //$tik->DEBUG_PRINT();
        return $tik;
    }


    private function _getChief($string)
    {
        if (mb_stripos($string, 'Председатель') !== false) {
            return $string;
        } else {
            return false;
        }
    }

    private function _getDeputy($string)
    {
        if (mb_stripos($string, 'Заместитель') !== false) {
            return $string;
        } else {
            return false;
        }
    }

    private function _getSecretary($string)
    {
        if (mb_stripos($string, 'Секретарь') !== false) {
            return $string;
        } else {
            return false;
        }
    }

    /**
     * @param string $string
     * @param boolean $stripTags
     * @return string
     */
    private function _clearStringData($string, $stripTags = true)
    {
        $string = trim($string);
        $string = str_replace('&nbsp;', '', $string);
        $string = html_entity_decode($string);
        if ($stripTags) {
            $string = strip_tags($string);
        }
        $string = iconv('cp1251', 'utf-8', iconv('utf-8', 'cp1251//ignore', $string));
        $string = trim($string);
        return $string;
    }
}