<?php
/**
 * action for (cron) tasks of importing violation feeds from various sources
 * @argv[1] - projectCode
 */
require_once('base.php');

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

$xmlProcessor = new ParserIKData_XMLProcessor_Violation_Krasnoyarsk($projectCode);
$gateway = new ParserIKData_Gateway_Violation_Krasnoyarsk();

$timeStart = microtime(true);

$sXml = $xmlProcessor->loadFromSource($projectFeed);

if (!$sXml instanceof SimpleXMLElement) {
    print('bad xml');
    return;
}
$timeEndLoad = microtime(true);


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
$timeEnd = microtime(true);
print_r($importCodes);
print PHP_EOL . sprintf('total time in sec: %.2F; load time: %.2F; our time: %.2F', ($timeEnd - $timeStart), ($timeEndLoad - $timeStart), ($timeEnd - $timeEndLoad));