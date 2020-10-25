<?php

function frsGetNoticeMessage($unix_group, $full_group, $package, $date, $group_id, $release_id, $package_id)
{
    global $xoopsConfig;

    $site = $xoopsConfig['sitename'];

    $message = [];

    $message['subject'] = "[$site " . _XF_FRS_RELEASE . '] ' . $unix_group . ' : ' . $package;

    $message['body'] = 'Project: ' . $full_group . '  (' . $unix_group . ")\n"

                       . 'Package: ' . $package . "\n"

                       . 'Date   : ' . $date . "\n"

                       . "\n"

                       . "Project '" . $full_group . "' ('" . $unix_group . "') has "

                       . "released the new version of package '" . $package . "'. "

                       . "You can download it from $site "

                       . "by following this link:\n"

                       . '<' . $xoopsConfig['xoops_url'] . '/modules/xfmod/project/showfiles.php?group_id=' . $group_id . '&release_id=' . $release_id . ">\n"

                       . "or browse Release Notes and ChangeLog by visiting this link:\n"

                       . '<' . $xoopsConfig['xoops_url'] . '/modules/xfmod/project/shownotes.php?release_id=' . $release_id . ">\n"

                       . "\n"

                       . 'You receive this email because you '

                       . 'requested to be notified when new versions of this package '

                       . "were released. If you don't wish to be notified in the "

                       . "future, please login to $site and click this link: "

                       . '<' . $xoopsConfig['xoops_url'] . '/modules/xfmod/project/filemodule_monitor.php?filemodule_id=' . $package_id . ">\n";

    return $message;
}

function grpGetApprovalMessage($unix_group, $full_group, $group_id, $type = 1)
{
    global $xoopsConfig;

    $site = $xoopsConfig['sitename'];

    $prjtype = (1 == $type ? 'project' : 'community');

    $uprjtype = (1 == $type ? 'Project' : 'Community');

    $plprjtype = (1 == $type ? 'projects' : 'communities');

    $message = [];

    $message['subject'] = $site . ' ' . $uprjtype . ' Approved';

    $message['body'] = 'Your ' . $prjtype . " registration for $site has been approved.\n"

                       . "\n"

                       . $uprjtype . ' Full Name: ' . $full_group . "\n"

                       . $uprjtype . ' Short Name: ' . $unix_group . "\n"

                       . "\n"

                       . 'Please take some time to read the site documentation about ' . $prjtype . ' '

                       . 'administration (<' . $xoopsConfig['xoops_url'] . '/modules/xfmod/help/' . $plprjtype . '.php#administering_' . $plprjtype . '>).'

                       . "\n"

                       . "We highly suggest that you now visit $site and create a public "

                       . 'description for your ' . $prjtype . '. This can be done by visiting your ' . $prjtype . ' '

                       . "page while logged in, and selecting '" . $uprjtype . " Admin' from the menus "

                       . 'on the top (or by visiting <' . $xoopsConfig['xoops_url'] . '/modules/xfmod/' . $prjtype . '/admin/?group_id=' . $group_id . '> '

                       . "after login)\n"

                       . "\n"

                       . 'NOTICE:  Your ' . $prjtype . ' will also not appear in the Trove Software Map (primary '

                       . 'list of ' . $plprjtype . " hosted on $site which offers great flexibility in "

                       . 'browsing and search) until you categorize it in the ' . $prjtype . ' administration '

                       . 'screens. So that people can find your ' . $prjtype . ', you should do this now. '

                       . 'Visit your ' . $prjtype . " while logged in, and select '" . $uprjtype . " Admin' from the "

                       . "menus on the top.\n"

                       . "\n"

                       . "We hope you will enjoy the system and tell others about $site. Let us know "

                       . "if there is anything we can do to help you.\n"

                       . "\n"

                       . " -- the $site group";

    return $message;
}

function grpGetDeniedMessage($unix_group, $full_group, $type = 1)
{
    global $xoopsConfig;

    $site = $xoopsConfig['sitename'];

    $prjtype = (1 == $type ? 'project' : 'community');

    $uprjtype = (1 == $type ? 'Project' : 'Community');

    $plprjtype = (1 == $type ? 'projects' : 'communities');

    $message = [];

    $message['subject'] = "$site " . $uprjtype . ' Denied';

    $message['body'] = 'Your ' . $prjtype . " registration for $site has been denied.\n"

                       . "\n"

                       . $uprjtype . ' Full Name: ' . $full_group . "\n"

                       . $uprjtype . ' Short Name: ' . $unix_group . "\n"

                       . "\n"

                       . "Reasons for negative decision:\n";

    return $message;
}

function frmGetMonitorMessage($msg_id, $uname, $body, $forum_id, $unix_group_name, $forum_name, $subject)
{
    global $xoopsConfig;

    $site = $xoopsConfig['sitename'];

    $message = [];

    $message['subject'] = '[' . $unix_group_name . ' - ' . $forum_name . '] ' . $subject;

    $message['body'] = "\n"

                       . "Read and respond to this message at:\n"

                       . '<' . $xoopsConfig['xoops_url'] . '/modules/xfmod/forum/message.php?msg_id=' . $msg_id . ">\n"

                       . 'By: ' . $uname . "\n"

                       . "\n"

                       . $body . "\n"

                       . "\n"

                       . "______________________________________________________________________\n"

                       . "You are receiving this email because you selected to monitor this forum.\n"

                       . "To stop monitoring this forum, login to $sitee and visit:\n"

                       . '<' . $xoopsConfig['xoops_url'] . '/modules/xfmod/forum/monitor.php?forum_id=' . $forum_id . '>';

    return $message;
}

function myGetDiaryMessage($uname, $summary, $details, $uid)
{
    global $xoopsConfig;

    $site = $xoopsConfig['sitename'];

    $message = [];

    $message['subject'] = '[ XF User Notes: ' . $uname . '] ' . stripslashes($summary);

    $message['body'] = "\n"

                       . stripslashes($details) . "\n"

                       . "\n"

                       . "______________________________________________________________________\n"

                       . "You are receiving this email because you selected to monitor this user.\n"

                       . "To stop monitoring this user, login to $site and visit:\n"

                       . '<' . $xoopsConfig['xoops_url'] . '/modules/xfmod/developer/monitor.php?diary_user=' . $uid . '>';

    return $message;
}
