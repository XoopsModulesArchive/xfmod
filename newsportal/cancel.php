<?php
/*
 with this script you can delete (cancel) articles.

 DO NOT USE IT, IF YOU DON'T KNOW WHAT A CANCEL IS!

 Especialy, don't use it in UseNet and protect it with a password (with
 .htaccess for example), or anybody can delete any article woldwide!
*/

require_once 'config.inc';

// register parameters
$newsgroups = $_REQUEST['newsgroups'];
$group = $_REQUEST['group'];
$group_id = $_REQUEST['group_id'];
$type = $_REQUEST['type'];
$subject = $_REQUEST['subject'];
$name = $_REQUEST['realname'];
$email = $_REQUEST['email'];
$body = $_REQUEST['body'];
$abspeichern = $_REQUEST['abspeichern'];
$references = $_REQUEST['references'];
$msg_id = $_REQUEST['msg_id'];

require_once 'head.inc';
require_once $file_newsportal;
// register parameters again
$newsgroups = $_REQUEST['newsgroups'];
$group = $_REQUEST['group'];
$group_id = $_REQUEST['group_id'];
$type = $_REQUEST['type'];
$subject = $_REQUEST['subject'];
$name = $_REQUEST['realname'];
$email = $_REQUEST['email'];
$body = $_REQUEST['body'];
$abspeichern = $_REQUEST['abspeichern'];
$references = $_REQUEST['references'];
$msg_id = $_REQUEST['msg_id'];

if (!$perm->isForumAdmin()) {
    redirect_header(XOOPS_URL . "/modules/xfmod/newsportal/thread.php?group_id=$group_id&group=$group", 4, 'Invalid Permissions');
}

if (!isset($type)) {
    $type = 'reply';
}

if (!isset($group)) {
    $group = $newsgroups;
}

// Is there a new article to be bost to the newsserver?
if ('cancel' == $type) {
    $show = 0;

    // error handling

    if ('' == trim($body)) {
        $type = 'retry';

        $error = $text_post['missing_message'];
    }

    if ('' == trim($forum_admin_email)) {
        $type = 'retry';

        $error = $text_post['missing_email'];
    }

    if (!validate_email(trim($forum_admin_email))) {
        $type = 'retry';

        $error = $text_post['error_wrong_email'];
    }

    if ('' == trim($realname)) {
        $type = 'retry';

        $error = $text_post['missing_name'];
    }

    if ('' == trim($subject)) {
        $type = 'retry';

        $error = $text_post['missing_subject'];
    }

    if ('cancel' == $type) {
        if (!$readonly) {
            require_once 'admin/utils.php';

            // post article to the newsserver

            $message = article_cancel(
                quoted_printable_encode(stripslashes($subject)),
                $forum_admin_email . ' (' . quoted_printable_encode($name) . ')',
                $newsgroups,
                $references,
                $body,
                $cancelid
            );

            // Article sent without errors?

            if ('240' == mb_substr($message, 0, 3)) {
                ?>

    <h1 align="center">Delete Message</h1>

    <p>The message was successfully deleted</p>

<p><a href="<?php echo $file_thread . '?group_id=' . $group_id . '&group=' . urlencode($group) . '">' . $text_post['button_back'] . '</a> ' . $text_post['button_back2'] . ' ' . urlencode($group) ?></p>
<?php
            } else {
                // article not accepted by the newsserver

                $type = 'retry';

                $error = $text_post['error_newsserver'] . "<br><pre>$message</pre>";
            }
        } else {
            echo $text_post['error_readonly'];
        }
    }
}

    // A reply of an other article.
    if ('reply' == $type) {
        $message = read_message($msg_id, 0, $group);

        $head = $message->header;

        $body = explode("\n", $message->body[0]);

        closeNNTPconnection($ns);

        $bodyzeile = "Reason for deletion:\n\n\n";

        if ('' != $head->name) {
            $bodyzeile .= $head->name;
        } else {
            $bodyzeile .= $head->from;
        }

        $bodyzeile .= " posted a message containing the following:\n";

        $bodyzeile .= "---------------------------------------\n\n";

        for ($i = 0; $i <= count($body) - 1; $i++) {
            $bodyzeile .= $body[$i] . "\n";
        }

        $subject = $head->subject;

        if (isset($head->followup) && ('' != $head->followup)) {
            $newsgroups = $head->followup;
        } else {
            $newsgroups = $head->newsgroups;
        }

        splitSubject($subject);

        $subject = 'Re: ' . $subject;

        // Cut off old parts of a subject

        // for example: 'foo (was: bar)' becomes 'foo'.

        $subject = eregi_replace('(\(wa[sr]: .*\))$', '', $subject);

        $show = 1;

        $references = false;

        if (isset($head->references[0])) {
            for ($i = 0; $i <= count($head->references) - 1; $i++) {
                $references .= $head->references[$i] . ' ';
            }
        }

        $references .= $head->id;
    }

    if ('retry' == $type) {
        $show = 1;

        $bodyzeile = $body;
    }

    if (1 == $show) {
        if ($testgroup) {
            $testnewsgroups = testGroups($newsgroups);
        } else {
            $testnewsgroups = $newsgroups;
        }

        if ('' == $testnewsgroups) {
            echo $text_post['followup_not_allowed'];

            echo ' ' . $newsgroups;
        } else {
            $newsgroups = $testnewsgroups;

            echo '<h1 align="center">Delete Message</h1>';

            echo '<p><b>Warning</b> This will permenately remove the message from all connected news servers.</b></p>';

            if (isset($error)) {
                echo "<p>$error</p>";
            } ?>

<br>

<form action="<?php echo $file_cancel ?>" method="get">

    <table>
        <tr>
            <td align="right" valign="top"><b>From:</b></td>
            <td align="left">
                <?php echo $xoopsUser->getVar('name') ?: $xoopsUser->getVar('uname'); ?><BR>
                <?php echo $xoopsUser->getVar('email'); ?><br>
                <input type="hidden" name="forum_admin_email" value="<?php echo $xoopsUser->getVar('email'); ?>">
                <input type="hidden" name="realname" value="<?php echo $xoopsUser->getVar('name') ?: $xoopsUser->getVar('uname'); ?>">
                <input type="hidden" name="subject" value="cancel <?php echo htmlentities(stripslashes($subject), ENT_QUOTES | ENT_HTML5); ?>">
            </td>
        </tr>
    </table>

<br>

    <table>
        <tr>
            <td><b><?php echo $text_post['message']; ?></b><br>
                <textarea name="body" rows="10" cols="79" wrap="physical">
<?php if (isset($bodyzeile)) {
                echo stripslashes($bodyzeile);
            } ?>
</textarea></td>
        </tr>
        <tr>
            <td>
                <input type="submit" value="Delete">
            </td>
        </tr>
    </table>
<input type="hidden" name="type" value="cancel">
<input type="hidden" name="cancelid" value="<?php echo $head->id; ?>">
<input type="hidden" name="newsgroups" value="<?php echo $newsgroups; ?>">
<input type="hidden" name="references" value="<?php echo htmlentities($references, ENT_QUOTES | ENT_HTML5); ?>">
<input type="hidden" name="group" value="<?php echo $group; ?>">
<input type="hidden" name="group_id" value="<?php echo $group_id; ?>">
    </form>

<?php
        }
    } ?>

<?php require_once 'tail.inc'; ?>
