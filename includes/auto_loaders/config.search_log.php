<?php

if (!defined('IS_ADMIN_FLAG')) {
 die('Illegal Access');
}
$autoLoadConfig[190][] = array('autoType'=>'class', 
'loadFile'=>'observers/class.search_log.php');//steve Dr. Byte changed 90->190 in 2.1.1 removed
$autoLoadConfig[190][] = array('autoType'=>'classInstantiate',
'className'=>'search_log','objectName'=>'search_log');//steve Dr. Byte changed 90->190 in 2.1.1 removed
 