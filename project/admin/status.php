<?php

require_once '../../../../mainfile.php';
$host = 'forge.novell.com';
$port = 80;
$path = '/api/publish/';
$querystring = "c=status&p=$id&s=" . session_id();
$xml = file('http://' . $host . ':' . $port . $path . '?' . $querystring);
$xml = implode('', $xml);

if (preg_match("/<publish id=\"\d+\" status=\"(\w+)\"/", $xml, $matches)) {
    switch ($matches[1]) {
        case 'active':
            echo "<html><head><meta http-equiv='refresh' content='1'></head><body>Please wait while your file is being published to Red Carpet &nbsp; ";
            $_SESSION['dots']++;
            $dots = $_SESSION['dots'] % 4;
            if (0 == $dots) {
                echo '\\';
            } elseif (1 == $dots) {
                echo '|';
            } elseif (2 == $dots) {
                echo '/';
            } elseif (3 == $dots) {
                echo '-';
            }
            echo '</body></html>';
            break;
        case 'succeeded':
            echo "Your file has been successfully published to Red Carpet.  <a href='javascript: window.close()'>Click here to close this window</a>.";
            unset($_SESSION['dots']);
            break;
        case 'failed':
            echo "Your file failed to be published to Red Carpet. ($xml)";
            unset($_SESSION['dots']);
            break;
        default:
            echo "display error: $xml";
    }
} else {
    echo "There was an error trying to get the status of your publish request: ($xml)";
}
