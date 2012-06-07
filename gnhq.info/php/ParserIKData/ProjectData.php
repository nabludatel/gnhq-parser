<?php
const PROJECT_GN = 'GN';
const PROJECT_GL = 'CL';
const PROJECT_GOLOS = 'AG';
const PROJECT_GRAKON = 'GR';
const PROJECT_LIGA = 'LI';
const PROJECT_ROSVYBORY = 'RV';
const PROJECT_SHTABPROHOROVA = 'MP';
const PROJECT_SMS_EXITPOLE = 'SE';

$PROJECT_CONFIG = array(
    PROJECT_GN => array(
            'Name'      => 'Гражданин наблюдатель',
			//'ProtoLink' => '',
            'ProtoLink' => 'http://gnhq.info/export/protocols.xml',
            'ViolLink'  => 'http://gnhq.info/export/violations.xml',
			'ViolLink'  => 'C:\git\gnhq.info\gnhq\gnhq.info\php\ParserIKData\Data\viol1.xml',
        ),
    PROJECT_GL => array(
            'Name'      => 'Гражданин наблюдатель чистые',
            // 'ProtoLink' => 'C:\git\gnhq.info\gnhq\gnhq.info\php\ParserIKData\Data\proto1.xml',
            'ProtoLink' => 'http://gnhq.info/export/protocols.xml',
            'ViolLink'  => '',
            //'ViolLink'  => 'C:\git\gnhq.info\gnhq\gnhq.info\php\ParserIKData\Data\viol1.xml',
    ),
	PROJECT_GOLOS => array(
            'Name'      => 'Голос',
			'ProtoLink' => '',
            'ProtoLink' => 'http://sms.golos.org/export.xml',
			'ViolLink'  => '',
            //'ViolLink'  => 'C:\git\gnhq.info\gnhq\gnhq.info\php\ParserIKData\Data\viol1.xml',
    ),
    PROJECT_SMS_EXITPOLE => array(
            'Name'      => 'СОТВ - Exit Pole',
            //'ProtoLink' => 'C:\git\gnhq.info\gnhq\gnhq.info\php\ParserIKData\Data\proto2.xml',
            'ProtoLink' => '',
			//'ProtoLink' => 'http://gnhq.info/export/allprotocols.xml',
            'ViolLink'  => '',
    ),
	PROJECT_LIGA => array(
            'Name'    => 'Лига избирателей',
            'ProtoLink' => '',
			'ViolLink'  => '',
    ),
	PROJECT_GRAKON => array(
            'Name'    => 'Гракон',
            'ProtoLink' => '',
			'ViolLink'  => '',
    ),
	PROJECT_SHTABPROHOROVA => array(
            'Name'    => 'Штаб Прохорова',
            'ProtoLink' => '',
			'ViolLink'  => '',
    ),
	PROJECT_ROSVYBORY => array(
            'Name'    => 'РосВыборы',
            'ProtoLink' => '',
			'ViolLink'  => '',
    ),
);