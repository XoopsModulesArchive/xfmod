<?php

$title = 'Newsportal - NNTP<->HTTP Gateway';

require_once 'head.inc';
require_once 'config.inc';

require (string)$file_newsportal;
flush();
$ns = OpenNNTPconnection($server, $port);

if (false !== $ns) {
    ?>
    <pre><?php $head = readPlainHeader($ns, $group, $id);

    for ($i = 0, $iMax = count($head); $i < $iMax; $i++) {
        echo $head[$i] . "\n";
    }

    $body = readMessage($ns, $id, '');

    for ($i = 0, $iMax = count($body); $i < $iMax; $i++) {
        echo $body[$i] . "\n";
    } ?></pre><?php
}
closeNNTPconnection($ns);

require_once 'tail.inc';
?>
