<?php
set_time_limit(0);
define('APPLICATION_DIR_ROOT', 'C:\git\gnhq.info\gnhq\gnhq.info\php\ParserIKData\\');

include_once '../../Lib/String/Extracter.php';

include_once '../../Lib/Html/DataMiner.php';
include_once '../../Lib/Html/Loader.php';
include_once '../../Lib/Html/Parser.php';


include_once '../Config.php';
include_once '../ServiceLocator.php';

include_once '../Site/Mosgor.php';

include_once '../Warehouse/Interface.php';
include_once '../Warehouse/Csv.php';

include_once '../Model.php';
include_once '../Model/Okrug.php';
include_once '../Model/TIK.php';
include_once '../Model/UIK.php';