<?php
class ParserIKData_XML_Violation extends ParserIKData_XML_Abstract
{
    /**
     * @param string $xml
     * @param string $projectCode
     * @return ParserIKData_Model_Violation|string
     */
    public function createFromXml($xml, $projectCode)
    {
        $errors = array();
        $sXml = simplexml_load_string($xml);
        $viol = ParserIKData_Model_Violation::create();

        // обязательные поля
        $viol->setProjectCode($projectCode);
        // id in project
        if (!$sXml->id) {
            $errors[] = 'Не указан id';
        } else {
            $viol->setProjectId($this->_filterString((string)$sXml->id, 50));
        }

        // update time
        if (!$sXml->updt) {
            $errors[] = 'Не указано время обновления';
        } else {
            $viol->setProjectUptime($this->_prepareTime((string)$sXml->updt));
        }

        // region
        if(!$sXml->region) {
            $errors[] = 'Не указан регион';
        } else {
            $viol->setRegionNum((int)$sXml->region);
        }
        // тип нарушения
        if (!$sXml->type) {
            $errors[] = 'Нет типа нарушения';
        } else {
            $viol->setMergedTypeId($this->_getMergedType($projectCode, (string)$sXml->type));
        }
        // description
        if (!$sXml->obscomment) {
            $errors[] = 'Нет описания';
        } else {
            $viol->setDescription($this->_filterString((string)$sXml->obscomment));
        }


        // необязательные поля с дефолтными значениями
        // complaint status
        if ($sXml->complaint) {
            $viol->setComplaintStatus($this->_filterString((string)$sXml->complaint, 1));
        } else {
            $viol->setComplaintStatus('n');
        }
        // obsrole
        if ($sXml->obsrole) {
            $viol->setObsrole($this->_filterString((string)$sXml->obsrole, 1));
        } else {
            $viol->setObsrole('n');
        }
        // version of the data
        if ($sXml->version) {
            $version = (int)$sXml->version;
        } else {
            $version = 0;
        }
        $viol->setProjectVersion($version);


        // необязательные поля без дефолтных значений
        if ($sXml->place) {
            $viol->setPlace($this->_filterString((string)$sXml->place, 100));
        }
        if ($sXml->impact) {
            $viol->setImpact((int)$sXml->impact);
        }
        if ($sXml->obstime) {
            $viol->setObstime($this->_prepareTime((string)$sXml->obstime));
        }
        if ($sXml->recchannel) {
            $channel = (string)$sXml->recchannel;
            if ($this->_prepareChannel($channel)) {
                $viol->setRecchanel($this->_prepareChannel($channel));
            }
        }
        if ($sXml->hqcomment) {
            $viol->setHqcomment($this->_filterString((string)$sXml->hqcomment));
        }
        if ($sXml->obsid) {
            $viol->setObsid($this->_filterString((string)$sXml->obsid, 50));
        }
        if ($sXml->obstrusted) {
            $viol->setObstrusted((int)$sXml->obstrusted);
        }
        if ($sXml->police) {
            $police = (string)$sXml->police;
            if ($this->_preparePoliceReaction($police)) {
                $viol->setPoliceReaction($this->_preparePoliceReaction($police));
            }
        }
        if ($sXml->rectified) {
            $rectified = (bool)$sXml->rectified;
            $viol->setRectified(intval($rectified));
        }

        if ($sXml->rectime) {
            $viol->setRectime($this->_prepareTime((string)$sXml->rectime));
        }

        // returning
        if (!empty($errors)) {
            return implode(', ' , $errors);
        } else {
            return $viol;
        }
    }

    private function _getMergedType($projectCode, $projectType)
    {
        $type = $this->_getTypeGateway()->findByProjectData($projectCode, $projectType);
        if (!$type) {
            return ParserIKData_Model_ViolationType::DEFAULT_MTYPE;
        } else {
            return $type->getMergedType();
        }
    }

    private function _getTypeGateway()
    {
        return new ParserIKData_Gateway_ViolationType();
    }

    private function _prepareChannel($channel)
    {
        if (is_numeric($channel)) {
            if (ParserIKData_Model_Violation::channelNameByCode($channel)) {
                return $channel;
            }
        } else {
            if (ParserIKData_Model_Violation::channelCodeByName(mb_strtolower($channel))) {
                return ParserIKData_Model_Violation::channelCodeByName(mb_strtolower($channel));
            }
        }
        return null;
    }

    private function _preparePoliceReaction($pr)
    {
        if (is_numeric($pr)) {
            if (ParserIKData_Model_Violation::policeReactionNameByCode($pr)) {
                return $pr;
            }
        } else {
            if (ParserIKData_Model_Violation::policeReactionCodeByName(mb_strtolower($pr))) {
                return ParserIKData_Model_Violation::policeReactionCodeByName(mb_strtolower($pr));
            }
        }
        return null;
    }
}