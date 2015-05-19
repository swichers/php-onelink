<?php
/**
 * @file
 */

use \StevenWichers\OneLink\OneLink;

// Need to change the current directory because the script can be run from
// anywhere and mess up the config autoloading.
chdir(__DIR__);

require '../vendor/autoload.php';

$onelink = new OneLink('otx', 'otxpass', 'otx', array('language' => 'es'));
$result = $onelink->translateText('I am going for a walk');


$result = $onelink->translateSegments(array(
    'one' => 'I am going for a walk.',
    'two' => 'I am going for a run.',
  ));
