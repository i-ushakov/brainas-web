#! /bin/bash

(cd common/config/; mv main-local.php main-local.php.tmp; mv main-local.php.test main-local.php)
(cd tests/codeception/frontend/; ../../../vendor/codeception/codeception/codecept run)
(cd common/config/; mv main-local.php main-local.php.test; mv main-local.php.tmp main-local.php)