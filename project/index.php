<?php

/**
 * project_home.php
 *
 * SourceForge: Breaking Down the Barriers to Open Source Development
 * Copyright 1999-2001 (c) VA Linux Systems
 * http://sourceforge.net
 *
 * @version   $Id: index.php,v 1.90 2004/07/15 18:29:27 danreese Exp $
 */
require_once '../../../mainfile.php';

$langfile = 'project.php';

require_once XOOPS_ROOT_PATH . '/modules/xfmod/include/pre.php';
require_once XOOPS_ROOT_PATH . '/modules/xfmod/include/vote_function.php';
require_once XOOPS_ROOT_PATH . '/modules/xfmod/include/vars.php';
require_once XOOPS_ROOT_PATH . '/modules/xfmod/news/news_utils.php';
require_once XOOPS_ROOT_PATH . '/modules/xfmod/include/trove.php';
require_once XOOPS_ROOT_PATH . '/modules/xfmod/include/project_summary.php';
require_once XOOPS_ROOT_PATH . '/modules/xfmod/maillist/maillist_utils.php';
require_once XOOPS_ROOT_PATH . '/modules/xfmod/newsportal/newsportal.php';

$GLOBALS['xoopsOption']['template_main'] = 'project/xfmod_index.html';

if (isset($_POST['group_id'])) {
    $group_id = $_POST['group_id'];
} elseif (isset($_GET['group_id'])) {
    $group_id = $_GET['group_id'];
} else {
    $group_id = null;
}

// Find project's group ID.
if (!$group_id || !is_numeric($group_id)) {
    $unixname = mb_strtolower(strtok($_SERVER['QUERY_STRING'], '&'));

    $sql = 'SELECT group_id' . ' FROM ' . $xoopsDB->prefix('xf_groups') . " WHERE unix_group_name='" . strtok($unixname, '&') . "'";

    $rs_group = $xoopsDB->query($sql);

    if ($rs_group && $xoopsDB->getRowsNum($rs_group) > 0) {
        $group_arr = $xoopsDB->fetchArray($rs_group);

        $group_id = $group_arr['group_id'];
    }
}

// Lookup project information.
$project = &group_get_object($group_id);
if (!$project) {
    redirect_header(XOOPS_URL, 2, _XF_PRJ_PROJECTDOESNOTEXIST);

    exit;
}

// Get user information.
$perm = &$project->getPermission($xoopsUser);

// Activate project, if requested.
if ($project->isInactive() && 'y' == $activate && $perm->isSuperUser()) {
    $sql = 'UPDATE ' . $xoopsDB->prefix('xf_groups') . " SET status='A', is_public=1" . ' WHERE group_id=' . $group_id;

    $result = $xoopsDB->queryF($sql);

    // Refresh the project object.

    $project = &group_get_object($group_id);

    $unixname = $project->getUnixName();
}

// If project is private, user must be a member or a superuser.
if (!$project->isPublic() && !$project->isMemberOfGroup($xoopsUser) && !$perm->isSuperUser()) {
    redirect_header(XOOPS_URL . '/', 4, _XF_PRJ_PROJECTMARKEDASPRIVATE);

    exit;
}

// If project is inactive, user must be a project admin or superuser.
if ($project->isInactive() && !$perm->isSuperUser() && !$perm->isAdmin()) {
    redirect_header(XOOPS_URL, 4, _XF_PRJ_NOTAUTHORIZEDTOENTER);

    exit;
}

// If project is dead, user must be a superuser.
if (!$project->isActive() && !$project->isInactive() && !$perm->isSuperUser()) {
    redirect_header(XOOPS_URL, 4, _XF_PRJ_NOTAUTHORIZEDTOENTER);

    exit;
}

// If the project is a community, redirect to community page.
if ($project->isFoundry()) {
    redirect_header(XOOPS_URL . "/modules/xfmod/community/?$unixname", 4, '');

    exit;
}

// Begin page construction.
include '../../../header.php';

// Meta tag information.
$metaTitle = $project->getPublicName() . ' ' . _XF_G_SUMMARY;
$metaKeywords = project_getmetakeywords($group_id);
$metaDescription = str_replace('"', '&quot;', strip_tags($project->getDescription()));

$xoopsTpl->assign('xoops_pagetitle', $metaTitle);
$xoopsTpl->assign('xoops_meta_keywords', $metaKeywords);
$xoopsTpl->assign('xoops_meta_description', $metaDescription);

// Project title and navigation.
$xoopsTpl->assign('project_name', project_title($project));
$xoopsTpl->assign('project_tabs', project_tabs('home', $group_id));

// If project is inactive and user is a superuser, display button to reactivate the project.
if ($project->isInactive() && 'y' != $activate && $perm->isSuperUser()) {
    $inactiveInfo = "\n<center>\n\t<p><b>"
                    . _XF_PRJ_THISPRJISINACTIVE
                    . "</b></p>\n"
                    . "\t<form action='/modules/xfmod/project/?$unixname' method='POST'>\n"
                    . "\t\t<input type='hidden' name='activate' value='y'>\n"
                    . "\t\t<input type='submit' name='submit' value='"
                    . _XF_PRJ_REACTIVATEPRJ
                    . "'>\n"
                    . "\t</form>\n</center>\n<hr>";

    $xoopsTpl->assign('inactive_info', $inactiveInfo);
}

// Display description.
$content = "<p>\n\t";
if ('H' == $project->getStatus()) {
    $content .= _XF_PRJ_ISMAINTAINEDBYSTAFF . "\n</p>\n<p>\n\t";
}
$desc = $project->getDescription();
$content .= ($desc ? $ts->displayTarea($desc) : _XF_PRJ_NODESCRIPTION) . "\n</p>\n";

// Display the license information based on $LICENSE from xfmod/include/vars.php.
$license = $project->getLicense();
if ('publicdomain' == $license) {
    $content .= '<p>' . _XF_PRJ_LICENSE_PUBLIC . "</p>\n";
} elseif ('other' == $license) {
    $content .= '<p>' . _XF_PRJ_LICENSE_OTHER . "</p>\n";
} else {
    $licenseTitle = $LICENSE[$license];

    $licenseURL = $LICENSE_DESCRIPTION[$license];

    $content .= '<p>' . _XF_PRJ_LICENSE_OPENSOURCE . "<a style='text-decoration:underline' href='$licenseURL'>$licenseTitle</a>.</p>\n";
}

// Get the activity percentile.
$sql = 'SELECT percentile' . ' FROM ' . $xoopsDB->prefix('xf_project_weekly_metric') . " WHERE group_id=$group_id";
$rs_activity = $xoopsDB->query($sql);
[$activity] = $xoopsDB->fetchArray($rs_activity);
if (!$activity) {
    $activity = 0;
}

// Get the number of CVS commits.
$sql = 'SELECT SUM(count) as total' . ' FROM ' . $xoopsDB->prefix('xf_cvs_commit_tracker') . " WHERE unix_group_name='$unixname'";
$rs_cvs = $xoopsDB->query($sql);
[$cvscommits] = $xoopsDB->fetchRow($rs_cvs);
if (!$cvscommits) {
    $cvscommits = 0;
}

// Display activity, CVS commits, and bookmark.
$content .= '<p>' . sprintf(_XF_PRJ_ACTIVITYPERCENT . ': %.0f%%', $activity) . '&nbsp;|&nbsp;' . _XF_PRJ_CVSCOMMITS . ': ' . $cvscommits;
$bm_url = XOOPS_URL . "/modules/xfmod/project/?$unixname";
$bm_title = $project->getPublicName();
$content .= "<br><a style='text-decoration:underline' href='../../xfaccount/bookmark_add.php?bookmark_url=$bm_url&bookmark_title=$bm_title'>" . _XF_PRJ_BOOKMARK . "</a></p>\n";

// Display help wanted.
$sql = 'SELECT DISTINCT name' . ' FROM ' . $xoopsDB->prefix('xf_people_job') . ' pj' . ', ' . $xoopsDB->prefix('xf_people_job_category') . ' pjc' . ' WHERE pj.category_id=pjc.category_id' . ' AND pj.status_id=1' . " AND group_id=$group_id";
$rs_jobs = $xoopsDB->query($sql, 2);
if ($rs_jobs) {
    $num = $xoopsDB->getRowsNum($rs_jobs);

    if ($num > 0) {
        $content .= '<p>' . _XF_PRJ_HELPWANTEDFOR_1 . ' ';

        $url = "/modules/xfjobs/?group_id=$group_id";

        if (1 == $num) {
            [$jobName] = $xoopsDB->fetchRow($rs_jobs);

            $content .= "<a href='$url'>$jobName(s)";
        } else {
            $content .= _XF_PRJ_HELPWANTEDFOR_2 . " <a href='$url'>" . _XF_PRJ_HELPWANTEDFOR_3;
        }

        $content .= "</a></p>\n";
    }
}

$xoopsTpl->assign('project_title', _XF_PRJ_DESCRIPTION);
$xoopsTpl->assign('project_content', $content);

// Display recent file releases.
$content = "<table border='0' cellspacing='1' cellpadding='5' width='100%'>
<tr class='bg2'>
	<td align='left'><b>" . _XF_PRJ_PACKAGE . "</b></td>
	<td align='center'><b>" . _XF_PRJ_RELEASE . "</b></td>
	<td align='center'><b>" . _XF_PRJ_RELEASEDATE . "</b></td>
	<td align='center'><b>" . _XF_PRJ_NOTES . ' / ' . _XF_PRJ_MONITOR . "</b></td>
</tr>\n";

$rights = ($perm->isMember() ? 3 : 2);
$sql = 'SELECT p.package_id,p.name AS package_name,r.name AS release_name,r.release_id AS release_id,r.release_date AS release_date'
          . ' FROM '
          . $xoopsDB->prefix('xf_frs_package')
          . ' p'
          . ', '
          . $xoopsDB->prefix('xf_frs_release')
          . ' r'
          . ' WHERE p.package_id=r.package_id'
          . " AND p.group_id=$group_id"
          . " AND p.status_id<=$rights"
          . " AND r.status_id<=$rights"
          . ' ORDER BY p.package_id,r.release_date DESC';
$rs_frs = $xoopsDB->query($sql);

if (!$rs_frs || $xoopsDB->getRowsNum($rs_frs) < 1) {
    // No file releases.

    $content .= $xoopsDB->error();

    $content .= "<tr class='bg3'><td colspan='5'><b>" . _XF_PRJ_THISPROJECTNOTRELEASED . '</b></td></tr>';
} else {
    // Result has ALL releases -- before displaying, test to be sure the package has changed.

    $lastID = false;

    while (false !== ($row = $xoopsDB->fetchArray($rs_frs))) {
        // Only show one release from each package.

        if ($lastID != $row['package_id']) {
            // Remember package ID for next time.

            $lastID = $row['package_id'];

            // Display release row.

            $rel_date = getdate($row['release_date']);

            $content .= "<tr class='bg3' align='center'>\n\t<td align='left'><b>"
                         . $ts->htmlSpecialChars($row['package_name'])
                         . "</b></td>\n\t<td>"
                         . "<a style='text-decoration:underline' href='/modules/xfmod/project/showfiles.php?group_id=$group_id&release_id="
                         . $row['release_id']
                         . "#selected'>"
                         . $ts->htmlSpecialChars($row['release_name'])
                         . "</a></td>\n\t<td>"
                         . $rel_date['month']
                         . ' '
                         . $rel_date['mday']
                         . ', '
                         . $rel_date['year']
                         . "</td>\n\t<td>"
                         . "<a href='/modules/xfmod/project/shownotes.php?group_id=$group_id&release_id="
                         . $row['release_id']
                         . "'><img src='/modules/xfmod/images/ic/manual16c.png' width='15' height='15' alt='"
                         . _XF_PRJ_RELEASENOTES
                         . "'></a> / <a href='/modules/xfmod/project/filemodule_monitor.php?filemodule_id="
                         . $row['package_id']
                         . "'><img src='/modules/xfmod/images/ic/mail16d.png' width='15' height='15' alt='"
                         . _XF_PRJ_MONITORTHISPACKAGE
                         . "'></a></td>\n</tr>\n";
        }
    }
}
$content .= "</table>\n" . "[&nbsp;<a style='text-decoration:underline' href='/modules/xfmod/project/showfiles.php?group_id=$group_id'>" . _XF_PRJ_VIEWALLPROJECTFILES . "</a>&nbsp;]\n";

$xoopsTpl->assign('file_title', _XF_PRJ_LATESTFILERELEASES);
$xoopsTpl->assign('file_content', $content);

// News
if ($project->usesNews()) {
    $sql = 'SELECT u.uname, u.name, nb.forum_id, nb.summary, nb.date, nb.details'
               . ' FROM '
               . $xoopsDB->prefix('users')
               . ' u'
               . ', '
               . $xoopsDB->prefix('xf_news_bytes')
               . ' nb'
               . " WHERE nb.group_id=$group_id"
               . " AND nb.is_approved <> '4' "
               . ' AND u.uid=nb.submitted_by '
               . ' ORDER BY nb.date DESC';

    $rs_news = $xoopsDB->query($sql, 1);

    $content = '';

    if (!$rs_news || $xoopsDB->getRowsNum($rs_news) < 1) {
        $content .= '<p><i>' . _XF_NWS_NONEWSITEMSFOUND . "</i></p>\n";

        $content .= $xoopsDB->error();
    } else {
        while (false !== ($row = $xoopsDB->fetchArray($rs_news))) {
            // Get the user's name and date.

            $name = (empty($row['name']) ? $row['uname'] : $row['name']);

            $date = date('M j, Y', $row['date']);

            // Construct news item.

            $content .= "<table border='0' cellspacing='0' cellpadding='0' width='100%'>
	<tr><td colspan='2'><b>" . $row['summary'] . "</b></td></tr>
	<tr><td colspan='2'><p>" . $row['details'] . "</p></td></tr>
	<tr><td align='left'><i>$name ($date)</i></td><td align='right'>[&nbsp;<a style='text-decoration:underline' href='/modules/xfmod/forum/forum.php?forum_id=" . $row['forum_id'] . "'>" . _XF_NWS_READMORECOMMENT . "</a>&nbsp;]</td></tr>
</table>
<br>\n";
        }

        $content = mb_substr($content, 0, -6) . "\n"; // Remove last <br> (but not newline).
    }

    $xoopsTpl->assign('news_title', _XF_PRJ_LATESTNEWS);

    $xoopsTpl->assign('news_content', $content);
}

// Documentation
if ($project->usesDocman()) {
    $sql = 'SELECT u.uid, u.uname, u.name, d.docid, d.title, d.description, d.updatedate'
               . ' FROM  '
               . $xoopsDB->prefix('xf_doc_data')
               . ' d'
               . ', '
               . $xoopsDB->prefix('xf_doc_groups')
               . ' g'
               . ', '
               . $xoopsDB->prefix('users')
               . ' u'
               . " WHERE g.group_id=$group_id"
               . ' AND d.doc_group=g.doc_group'
               . ' AND d.created_by=u.uid'
               . ' AND d.stateid=1'
               . ' ORDER BY updatedate DESC';

    $rs_docs = $xoopsDB->query($sql, 3);

    $content = '';

    if (!$rs_docs || $xoopsDB->getRowsNum($rs_docs) < 1) {
        $content .= '<p><i>' . _XF_PRJ_NO_DOCUMENTS_FOUND . "</i></p>\n";

        $content .= $xoopsDB->error();
    } else {
        while (false !== ($row = $xoopsDB->fetchArray($rs_docs))) {
            // Get the user's name and date.

            $name = (empty($row['name']) ? $row['uname'] : $row['name']);

            $date = date('M j, Y', $row['updatedate']);

            // Construct doc item.

            $content .= "<table border='0' cellspacing='1' cellpadding='5' width='100%'>
	<tr><td colspan='2'><b>" . $row['title'] . "</b></td></tr>
	<tr><td colspan='2'><p>" . $row['description'] . "</p></td></tr>
	<tr>
		<td align='left'><i>$name ($date)</i></td>
		<td align='right'>[&nbsp;<a style='text-decoration:underline' href='/modules/xfmod/docman/?group_id=$group_id&docid=" . $row['docid'] . "'>" . _XF_PRJ_DOWNLOAD . "</a>&nbsp;]</td>
	</tr>
</table>
<br>\n";
        }

        $content = mb_substr($content, 0, -6) . "\n"; // Remove last <br> (but not newline).
    }

    $xoopsTpl->assign('doc_title', _XF_PRJ_LATEST_DOCUMENTATION);

    $xoopsTpl->assign('doc_content', $content);
}

// Surveys
// NOTE: Intentionally turned off for now.  This code doesn't work anyway.
if (false && $project->usesSurvey()) {
    $sql = 'SELECT *' . ' FROM ' . $xoopsDB->prefix('xf_surveys') . " WHERE group_id=$group_id" . ' AND is_active=1' . ' ORDER BY survey_id DESC';

    $rs_surveys = $xoopsDB->query($sql, 1);

    $rows = $xoopsDB->getRowsNum(rs_surveys);

    $content = "<table border='0' cellspacing='0' cellpadding='0' width='100%'>\n" . '<tr><td>';

    $content .= "<a href='/modules/xfmod/survey/?group_id=" . $group_id . "'>";

    $content .= "<img src='/modules/xfmod/images/ic/survey16b.png' width='20' height='20' alt='" . _XF_G_SURVEYS . "'>";

    $content .= ' ' . _XF_G_SURVEYS . '</A>';

    $content .= ' ( <b>' . project_get_survey_count($group_id) . '</b> ' . _XF_PRJ_SURVEYS . ' )';

    $content .= "<hr size='1' noshade='true'>";

    $xoopsTpl->assign('survey_title', _XF_PRJ_SURVEYS);

    $xoopsTpl->assign('survey_content', $content);
}

// Member Information
$sql = 'SELECT u.uid, u.uname, u.name' . ' FROM ' . $xoopsDB->prefix('users') . ' u' . ', ' . $xoopsDB->prefix('xf_user_group') . ' ug' . ' WHERE ug.user_id=u.uid' . " AND ug.group_id=$group_id" . " AND ug.admin_flags='A'" . ' AND u.level>0';
$rs_admins = $xoopsDB->query($sql);

$content = "<table border='0' cellspacing='0' cellpadding='0' width='100%'>
<tr>
	<td align='left'><b>" . _XF_PRJ_PROJECTADMINS . "</b></td>
	<td align='right'>[&nbsp;<a style='text-decoration:underline' href='/modules/xfmod/project/memberlist.php?group_id=$group_id'>" . _XF_PRJ_VIEWMEMBERS . "</a>&nbsp;]</td>
</tr>
<tr>
	<td colspan='2'><p>";
while (false !== ($row = $xoopsDB->fetchArray($rs_admins))) {
    $name = (empty($row['name']) ? $row['uname'] : $row['name']);

    $content .= "\n<a style='text-decoration:underline' href='/userinfo.php?uid=" . $row['uid'] . "'>$name</a>,";
}
$content = rtrim($content, ','); // Remove last comma.
$content .= "</p></td>\n</tr>\n</table>\n";

$xoopsTpl->assign('member_title', _XF_PRJ_DEVELOPERSINFO);
$xoopsTpl->assign('member_content', $content);

// Forums
if ($project->usesForum()) {
    $sql = 'SELECT forum_name, forum_desc_name' . ' FROM ' . $xoopsDB->prefix('xf_forum_nntp_list') . " WHERE group_id=$group_id";

    $rs_forums = $xoopsDB->query($sql);

    $content = '';

    if (!$rs_forums || $xoopsDB->getRowsNum($rs_forums) < 1) {
        $content .= '<p><i>' . _XF_PRJ_NO_FORUMS_FOUND . "</i></p>\n";

        $content .= $xoopsDB->error();
    } else {
        while (false !== ($row = $xoopsDB->fetchArray($rs_forums))) {
            // Get forum name and number of posts.

            $name = $row['forum_name'];

            $posts = getNumArticles($ns, $name);

            // Construct forum item.

            $content .= "<table border='0' cellspacing='1' cellpadding='5' width='100%'>
	<tr>
		<td align='left'><b>" . $row['forum_desc_name'] . "</b></td>
		<td align='right' valign='top'>[&nbsp;<a style='text-decoration:underline' href='/modules/xfmod/newsportal/thread.php?group_id=$group_id&group=$name'>" . _XF_PRJ_VIEW_FORUM . "</a>&nbsp;]</td>
	</tr>
	<tr><td colspan='2' style='padding-left:1em'>($name)</td></tr>
</table>
<br>\n";
        }

        $content = mb_substr($content, 0, -6) . "\n"; // Remove last <br> (but not newline).
    }

    $xoopsTpl->assign('forum_title', _XF_PRJ_WEB_FORUMS);

    $xoopsTpl->assign('forum_content', $content);
}

// Mailing Lists
if ($project->usesMail()) {
    $sql = 'SELECT name, description' . ' FROM ' . $xoopsDB->prefix('xf_maillists') . " WHERE group_id=$group_id" . ' ORDER BY name ASC';

    $rs_lists = $xoopsDB->query($sql);

    $content = '';

    if (!$rs_lists || $xoopsDB->getRowsNum($rs_lists) < 1) {
        $content .= '<p><i>' . _XF_PRJ_NO_LISTS_FOUND . "</i></p>\n";

        $content .= $xoopsDB->error();
    } else {
        while (false !== ($row = $xoopsDB->fetchArray($rs_lists))) {
            // Get the suffix and date.

            $suffix = $row['name'];

            $date = date('M j, Y', $row['updatedate']);

            // Construct list item.

            $content .= "<table border='0' cellspacing='1' cellpadding='5' width='100%'>
	<tr>
		<td align='left'><b>$unixname-$suffix</b></td>
		<td align='right'>[&nbsp;<a style='text-decoration:underline' href='/modules/xfmod/maillist/archbrowse.php/$unixname-$suffix/?id=$group_id&prjname=$unixname&mlname=$suffix'>"
                        . _XF_PRJ_VIEW_ARCHIVE
                        . "</a>&nbsp;/&nbsp;<a style='text-decoration:underline' href='/modules/xfmod/maillist/subscribe.php?group_id=$group_id&list="
                        . urlencode("$unixname-$suffix")
                        . "'>"
                        . _XF_PRJ_SUBSCRIBE
                        . "</a>&nbsp;]</td>
	</tr>
	<tr><td colspan='2' style='padding-left:1em'>"
                        . $row['description']
                        . "</td></tr>
</table>
<br>\n";
        }

        $content = mb_substr($content, 0, -6) . "\n"; // Remove last <br> (but not newline).
    }

    $xoopsTpl->assign('list_title', _XF_PRJ_MAILING_LISTS);

    $xoopsTpl->assign('list_content', $content);
}

// Communities
$sql = 'SELECT g.unix_group_name AS community_unixname, g.group_name AS community_name, g.short_description'
                  . ' FROM '
                  . $xoopsDB->prefix('xf_groups')
                  . ' g'
                  . ', '
                  . $xoopsDB->prefix('xf_trove_group_link')
                  . ' t'
                  . " WHERE t.group_id=$group_id"
                  . ' AND g.group_id=t.trove_cat_id'
                  . ' AND g.group_id!=2';
$rs_communities = $xoopsDB->query($sql);

$content = '';
if (!$rs_communities || $xoopsDB->getRowsNum($rs_communities) < 1) {
    $content .= '<p><i>' . _XF_PRJ_NO_COMMUNITIES_FOUND . "</i></p>\n";

    $content .= $xoopsDB->error();
} else {
    while (false !== ($row = $xoopsDB->fetchArray($rs_communities))) {
        // Construct community item.

        $content .= "<table border='0' cellspacing='1' cellpadding='5' width='100%'>
	<tr>
		<td align='left'><b>" . $row['community_name'] . "</b></td>
		<td align='right'>[&nbsp;<a style='text-decoration:underline' href='/modules/xfmod/community/?" . $row['community_unixname'] . "'>" . _XF_PRJ_VIEW_COMMUNITY . "</a>&nbsp;]</td>
	</tr>
	<tr><td colspan='2' style='padding-left:1em'>" . $row['short_description'] . "</td></tr>
</table>
<br>\n";
    }

    $content = mb_substr($content, 0, -6) . "\n"; // Remove last <br> (but not newline).
}

$xoopsTpl->assign('community_title', _XF_PRJ_COMMUNITIES);
$xoopsTpl->assign('community_content', $content);

// Footer
include '../../../footer.php';
