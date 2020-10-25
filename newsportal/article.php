<?php

require_once 'config.inc';
require_once '../include/utils.php';

// register parameters
$msg_id = $_REQUEST['msg_id'];
$mygroup = $_REQUEST['group'];

require_once (string)$file_newsportal;

$message = read_message($msg_id, 0, $mygroup);
if (!$message) {
    redirect_header($GLOBALS['HTTP_REFERER'], 4, 'Article no found');

    exit;
}

$subject = htmlspecialchars($message->header->subject, ENT_QUOTES | ENT_HTML5);
$title .= ' - ' . $subject;

require_once 'head.inc';
?>

<p align="center">
    <?php
    echo "[<a href='javascript:history.back();'>Back</a>]\n";
    if ((!$readonly) && ($message)) {
        if (null != $xoopsUser) {
            echo '[<a href="' . $file_post . '?group_id=' . $group_id . '&type=reply&msg_id=' . urlencode($msg_id) . '&group=' . urlencode($mygroup) . '">' . $text_article['button_answer'] . '</a>]' . "\n";
        } else {
            echo '[<a href="' . XOOPS_URL . '/user.php?xoops_redirect=' . $_SERVER['PHP_SELF'] . '?' . urlencode($_SERVER['QUERY_STRING']) . '">Log in to reply</a>]';
        }
    }
    if ($perm->isForumAdmin()) {
        echo '[<a href="' . $file_cancel . '?group_id=' . $group_id . '&type=reply&msg_id=' . urlencode($msg_id) . '&group=' . urlencode($mygroup) . '">Delete Message</a>]' . "\n";
    }
    echo '[<a  href="' . $file_thread . '?group_id=' . $group_id . '&group=' . urlencode($mygroup) . '">' . $mygroup . '</a>]';
    ?>
</p>

<?php
if (!$message) {// article not found
        echo $text_error['article_not_found'];
    } else {
        if ($article_showthread) {
            $thread = loadThreadData($mygroup, $msg_id);
        }

        show_article($mygroup, $msg_id, 0, $message);

        if ($article_showthread) {//try to pass in the whole header here, not just the id so inside I can see if there are attachments.
            message_thread($message->header->id, $mygroup, $thread);
        }
    }
require_once 'tail.inc';
?>
