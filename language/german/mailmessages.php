<?php

function frsGetNoticeMessage($unix_group, $full_group, $package, $date, $group_id, $release_id, $package_id)
{
    global $xoopsConfig;

    $site = $xoopsConfig['sitename'];

    $message = [];

    $message['subject'] = "[$site " . _XF_FRS_RELEASE . '] ' . $unix_group . ' : ' . $package;

    $message['body'] = 'Projekt: '
                       . $full_group
                       . '  ('
                       . $unix_group
                       . ")\n"
                       . 'Paket: '
                       . $package
                       . "\n"
                       . 'Datum   : '
                       . $date
                       . "\n"
                       . "\n"
                       . "Projekt '"
                       . $full_group
                       . "' ('"
                       . $unix_group
                       . "') hat "
                       . "eine neue Version des Pakets '"
                       . $package
                       . "'. veröffentlicht "
                       . "Du kannst es herunterladen von $site "
                       . "wenn du diesem Link folgst:\n"
                       . '<'
                       . XOOPS_DEFAULT_DOMAIN
                       . XOOPS_URL
                       . '/modules/xfmod/project/showfiles.php?group_id='
                       . $group_id
                       . '&release_id='
                       . $release_id
                       . ">\n"
                       . "oder durchsuche die Release-Notizen und ChangeLogs nber diesen Link:\n"
                       . '<'
                       . XOOPS_DEFAULT_DOMAIN
                       . XOOPS_URL
                       . '/modules/xfmod/project/shownotes.php?release_id='
                       . $release_id
                       . ">\n"
                       . "\n"
                       . 'Du erhSltst diese E-Mail weil du '
                       . 'benachrichtigt werden wolltest wenn neue Versionen dieses Pakets '
                       . 'veröffentlicht werden. Falls du zuknnftig nicht mehr benachrichtigt werden willst '
                       . "logge dich auf $site ein und klicke auf diesen Link: "
                       . '<'
                       . XOOPS_DEFAULT_DOMAIN
                       . XOOPS_URL
                       . '/modules/xfmod/project/filemodule_monitor.php?filemodule_id='
                       . $package_id
                       . ">\n";

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

    $message['subject'] = $site . ' ' . $uprjtype . ' Freigegeben';

    $message['body'] = 'Deine '
                       . $prjtype
                       . " Registrierung auf $site wurde freigegeben.\n"
                       . "\n"
                       . $uprjtype
                       . ' Langname: '
                       . $full_group
                       . "\n"
                       . $uprjtype
                       . ' Kurzname: '
                       . $unix_group
                       . "\n"
                       . "\n"
                       . 'Nimm dir etwas Zeit und lese die Site-Dokumentation nber '
                       . $prjtype
                       . ' '
                       . 'Administration (<'
                       . XOOPS_DEFAULT_DOMAIN
                       . XOOPS_URL
                       . '/modules/xfmod/help/'
                       . $plprjtype
                       . '.php#administering_'
                       . $plprjtype
                       . '>).'
                       . "\n"
                       . "Wir möchten dir dringend empfehlen $site jetzt zu besuchen und eine öffentliche "
                       . 'Beschreibung fnr dein '
                       . $prjtype
                       . ' anzugeben. Das kannst du auf der '
                       . $prjtype
                       . ''
                       . "Seite machen, wenn du eingeloggt bist und '"
                       . $uprjtype
                       . " Admin' aus den Menns "
                       . 'oben auswShlst (oder durch den Besuch von <'
                       . XOOPS_DEFAULT_DOMAIN
                       . XOOPS_URL
                       . '/modules/xfmod/'
                       . $prjtype
                       . '/admin/?group_id='
                       . $group_id
                       . '> '
                       . "nach dem Login)\n"
                       . "\n"
                       . 'HINWEIS:  Dein '
                       . $prjtype
                       . ' wird auch nicht auf der Fund Software Map auftauchen (primSre '
                       . 'Liste von '
                       . $plprjtype
                       . " gehostet auf $site welche sehr flexibel ist beim "
                       . 'durchstöbern und suchen) bis du es kategorisierst auf den '
                       . $prjtype
                       . ' Administrationsseiten'
                       . 'Damit die User dein '
                       . $prjtype
                       . ' finden können, solltest du das jetzt tun. '
                       . 'Besuche dein '
                       . $prjtype
                       . " wenn du eingeloggt bist und wShle '"
                       . $uprjtype
                       . " Admin' aus "
                       . "den Menns oben.\n"
                       . "\n"
                       . "Wir hoffen du hast Spa_ mit diesem System und berichtest anderen von $site. Lass es uns wissen "
                       . "wenn wir dir irgendwie helfen können.\n"
                       . "\n"
                       . " -- Die $site Gruppe";

    return $message;
}

function grpGetDeniedMessage($unix_group, $full_group, $type = 1)
{
    global $xoopsConfig;

    $site = $xoopsConfig['sitename'];

    $prjtype = (1 == $type ? 'project' : 'Community');

    $uprjtype = (1 == $type ? 'Project' : 'Community');

    $plprjtype = (1 == $type ? 'projects' : 'Communities');

    $message = [];

    $message['subject'] = "$site " . $uprjtype . ' Abgelehnt';

    $message['body'] = 'Deine ' . $prjtype . " Registrierung auf $site wurde abgelehnt.\n" . "\n" . $uprjtype . ' Langname: ' . $full_group . "\n" . $uprjtype . ' Kurzname: ' . $unix_group . "\n" . "\n" . "Grnnde fnr die Ablehnung:\n";

    return $message;
}

function frmGetMonitorMessage($msg_id, $uname, $body, $forum_id, $unix_group_name, $forum_name, $subject)
{
    global $xoopsConfig;

    $site = $xoopsConfig['sitename'];

    $message = [];

    $message['subject'] = '[' . $unix_group_name . ' - ' . $forum_name . '] ' . $subject;

    $message['body'] = "\n"
                       . "Lese und reagiere auf diese Nachricht auf:\n"
                       . '<'
                       . XOOPS_DEFAULT_DOMAIN
                       . XOOPS_URL
                       . '/modules/xfmod/forum/message.php?msg_id='
                       . $msg_id
                       . ">\n"
                       . 'Von: '
                       . $uname
                       . "\n"
                       . "\n"
                       . $body
                       . "\n"
                       . "\n"
                       . "______________________________________________________________________\n"
                       . "Du erhSltst diese E-Mail weil du dieses Forum beobachten wolltest.\n"
                       . "Um die Beobachtung abzuschalten logge dich auf $sitee ein und besuche:\n"
                       . '<'
                       . XOOPS_DEFAULT_DOMAIN
                       . XOOPS_URL
                       . '/modules/xfmod/forum/monitor.php?forum_id='
                       . $forum_id
                       . '>';

    return $message;
}

function myGetDiaryMessage($uname, $summary, $details, $uid)
{
    global $xoopsConfig;

    $site = $xoopsConfig['sitename'];

    $message = [];

    $message['subject'] = '[ XF-User-Notizen: ' . $uname . '] ' . stripslashes($summary);

    $message['body'] = "\n"
                       . stripslashes($details)
                       . "\n"
                       . "\n"
                       . "______________________________________________________________________\n"
                       . "Du erhSltst diese E-Mail weil du diesen User beobachten wolltest.\n"
                       . "Um die Beobachtung anzuschalten logge dich auf $site ein und besuche:\n"
                       . '<'
                       . XOOPS_DEFAULT_DOMAIN
                       . XOOPS_URL
                       . '/modules/xfmod/developer/monitor.php?diary_user='
                       . $uid
                       . '>';

    return $message;
}
