<?php

require_once 'header.php';
require_once 'cvs_services_processor.php';

$userServicesProcessor = new CvsServicesProcessor();
$userServicesProcessor->run();
