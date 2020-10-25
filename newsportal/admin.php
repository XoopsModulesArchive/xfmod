<?php

require_once '../config.inc';
require_once "../$file_newsportal";
$title .= 'News Portal Admin';
require_once '../head.inc';

$groupname = $_REQUEST['groupname'];
$action = $_REQUEST['action'];
if ($groupname && $action) {
    $message = control_group($action, $groupname);

    if ('240' == mb_substr($message, 0, 3)) {
        echo 'The newsgroup was successfully ';

        echo ('newgroup' == $action) ? 'added.' : 'removed.';
    } else {
        echo $message . "\n<BR>";
    }
}
?>
<FORM action="admin.php" method="POST">
    Action: <SELECT name="action">
        <OPTION value="newgroup">newgroup
        <OPTION value="rmgroup">rmgroup
    </SELECT>
    <br>
    Group Name: <INPUT type="text" name="groupname">
    <br>
    <INPUT type="submit" value="Submit">
</FORM>

<?php require_once '../tail.inc'; ?>
