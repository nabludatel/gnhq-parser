<?php
include_once 'include.php';

$projectCode = $argv[1];

if (empty($PROJECT_CONFIG[$projectCode])) {
    print 'wrong code '.$projectCode;
    return;
}

$projectFeed = $PROJECT_CONFIG[$projectCode]['ViolLink'];

if (!$projectFeed) {
    print 'no feed link';
    return;
}

$sXml = simplexml_load_file($projectFeed);
if (!$sXml instanceof SimpleXMLElement) {
    print('bad xml');
    return;
}


$xmlProcessor = new ParserIKData_XMLProcessor_Violation($projectCode);
$gateway = new ParserIKData_Gateway_Violation();

$importCodes = array();
foreach ($sXml->xpath('viol') as $vXml) {

    $newViol = $xmlProcessor->createFromXml($vXml);

    if (!$newViol instanceof ParserIKData_Model_Violation) {
        @$importCodes['invalid data' . $newViol]++;
        continue;
    }
    $result = $xmlProcessor->updateIfNecessary($newViol);
    @$importCodes[$result]++;
}

print_r($importCodes);