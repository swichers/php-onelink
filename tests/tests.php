<?php
/**
 * @file
 */

use \StevenWichers\OneLink\OneLink;

// Need to change the current directory because the script can be run from
// anywhere and mess up the config autoloading.
chdir(__DIR__);

require '../vendor/autoload.php';

$translate_string = 'I am going for a walk';

$onelink = new OneLink('otx', 'otxpass', 'otx', array('language' => 'es'));
$result = $onelink->translateText($translate_string);

echo 'Running tests on: ', $translate_string, PHP_EOL;

assert_options(ASSERT_ACTIVE, true);

assert($result->getData() == '<p>Yo soy va para un caminar</p>', 'String should have been translated.');
assert($result->isTranslated() == true, 'isTranslated should be true.');
assert($result->isSuccessful() == true, 'isSuccessful should be true.');
assert($result->isFailure() == false, 'isFailure should be false.');
assert($result->getTranslationPercent() == 100, 'String should have been fully translated.');

echo 'Done', PHP_EOL;
