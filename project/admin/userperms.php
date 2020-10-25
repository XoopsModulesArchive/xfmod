<?php
/**
 * Project Admin page to view permissions of the project members
 *
 * This pages shows permissions of all project members as static table,
 * with links to userpermedit.php for editing individual members.
 *
 * Known bugs:
 * 1. This page doesn't show permissions for specific trackers.
 *
 * SourceForge: Breaking Down the Barriers to Open Source Development
 * Copyright 1999-2001 (c) VA Linux Systems
 * http://sourceforge.net
 *
 * @version   $Id: userperms.php,v 1.10 2003/12/09 15:04:00 devsupaul Exp $
 */
require_once '../../../../mainfile.php';

$langfile = 'project.php';
require_once XOOPS_ROOT_PATH . '/modules/xfmod/include/pre.php';
require_once XOOPS_ROOT_PATH . '/modules/xfmod/project/admin/project_admin_utils.php';
require_once XOOPS_ROOT_PATH . '/modules/xfmod/include/project_summary.php';
$GLOBALS['xoopsOption']['template_main'] = 'project/admin/xfmod_userperms.html';

$group_id = http_get('group_id');
project_check_access($group_id);

// get current information
$group = &group_get_object($group_id);
$perm = &$group->getPermission($xoopsUser);

if (!$perm->isAdmin()) {
    redirect_header($GLOBALS['HTTP_REFERER'], 4, _XF_G_PERMISSIONDENIED . '<br>' . _XF_PRJ_NOTADMINTHISPROJECT);

    exit();
}

if ($group->isFoundry()) {
    define('_LOCAL_XF_G_PROJECT', _XF_G_COMM);

    define('_LOCAL_XF_PRJ_PROJECTDEVPERMISSIONS', _XF_COMM_COMMDEVPERMISSIONS);

    define('_LOCAL_XF_PRJ_NODEVELOPERSFOUND', _XF_COMM_NODEVELOPERSFOUND);
} else {
    define('_LOCAL_XF_G_PROJECT', _XF_G_PROJECT);

    define('_LOCAL_XF_PRJ_PROJECTDEVPERMISSIONS', _XF_PRJ_PROJECTDEVPERMISSIONS);

    define('_LOCAL_XF_PRJ_NODEVELOPERSFOUND', _XF_PRJ_NODEVELOPERSFOUND);
}

/*
 *	Main Code
 */

$group->clearError();

include '../../../../header.php';
$xoopsTpl->assign('project_title', project_title($group));
$xoopsTpl->assign('project_tabs', project_tabs('admin', $group_id));
$xoopsTpl->assign('project_admin_header', get_project_admin_header($group_id, $perm, $group->isProject()));

$xoopsTpl->assign('info', _XF_PRJ_CLICKNAMEBELOW);
$xoopsTpl->assign('general', _XF_PRJ_GENERAL);
$xoopsTpl->assign('tracker', _XF_PRJ_TRACKERMANAGER);
$xoopsTpl->assign('task', _XF_PRJ_PROJECTTASKMANAGER);
$xoopsTpl->assign('forums', _XF_G_FORUMS);
$xoopsTpl->assign('doc', _XF_PRJ_DOCMANAGER);
$xoopsTpl->assign('sample', _XF_PRJ_SAMPLEMANAGER);
$xoopsTpl->assign('isProject', $group->isProject());

$content = '';
$sql = 'SELECT u.uname AS user_name,'
           . ' u.name,'
           . ' u.uid AS user_id,'
           . ' ug.admin_flags,'
           . ' ug.forum_flags,'
           . ' ug.project_flags,'
           . ' ug.doc_flags,'
           . ' ug.sample_flags,'
           . ' ug.cvs_flags,'
           . ' ug.release_flags,'
           . ' ug.artifact_flags,'
           . ' ug.member_role '
           . ' FROM '
           . $xoopsDB->prefix('users')
           . ' u,'
           . $xoopsDB->prefix('xf_user_group')
           . ' ug '
           . ' WHERE '
           . ' u.uid=ug.user_id AND '
           . " ug.group_id='$group_id' "
           . ' ORDER BY u.uname';
$res_dev = $xoopsDB->query($sql);

if (!$res_dev || $xoopsDB->getRowsNum($res_dev) < 1) {
    $content .= '<TR><TD><H4>' . _LOCAL_XF_PRJ_NODEVELOPERSFOUND . '</H4></TD></TR>';
} else {
    $i = 0;

    while (false !== ($row_dev = $xoopsDB->fetchArray($res_dev))) {
        $content .= show_permissions_row($i++, $row_dev, $group->isProject());
    }
}

$xoopsTpl->assign('content', $content);
include '../../../../footer.php';

// Maps role id to string
function role_id2str($role_id)
{
    global $member_roles_assoc, $xoopsDB;

    if (!$member_roles_assoc) {
        $sql = 'SELECT category_id,name FROM ' . $xoopsDB->prefix('xf_people_job_category');

        $member_roles = $xoopsDB->query($sql);

        $member_roles_assoc = util_result_columns_to_assoc($member_roles);
    }

    if ($member_roles_assoc[$role_id]) {
        $str = $member_roles_assoc[$role_id];

        if (trim($str)) {
            return $str;
        }
    }

    return '???';
}

// Render table row of developer's permissions
function show_permissions_row($i, $row_dev, $is_project)
{
    global $group_id;

    $content = '';

    // Show admins in bold

    if (mb_stristr($row_dev['admin_flags'], 'A')) {
        $name = '<b>' . $row_dev['user_name'] . ' (' . $row_dev['name'] . ')</b>';
    } else {
        $name = $row_dev['user_name'] . ' (' . $row_dev['name'] . ')';
    }

    $content .= "<tr align='center' class='"
                . ($i % 2 > 0 ? 'bg1' : 'bg3')
                . "'>"
                . "<td align='left'>"
                . "<a href='userpermedit.php?group_id="
                . $group_id
                . '&user_id='
                . $row_dev['user_id']
                . "'>"
                . $name
                . '</a><br>'
                . role_id2str($row_dev['member_role'])
                . (1 == $row_dev['release_flags'] ? ', '
                                                    . _XF_PRJ_RELTECH : '')
                . '</td>';

    if ($is_project) {
        // artifact manager permissions

        $art2perm = [0 => '-', 2 => 'A'];

        $content .= '<TD>' . $art2perm[$row_dev['artifact_flags']] . '</td>';

        // project/task manager permissions

        $flag2perm = [0 => '-', 1 => 'T', 2 => 'A&T', 3 => 'A'];

        $content .= '<TD>' . $flag2perm[$row_dev['project_flags']] . '</td>';
    }

    // forum permissions

    $forum2perm = [0 => '-', 2 => _XF_PRJ_MODERATOR];

    $content .= '<TD>' . $forum2perm[$row_dev['forum_flags']] . '</td>';

    // documenation manager permissions

    $forum2perm = [0 => '-', 1 => _XF_PRJ_EDITOR];

    $content .= '<TD>' . $forum2perm[$row_dev['doc_flags']] . '</td>';

    // sample code manager permissions

    $forum2perm = [0 => '-', 1 => _XF_PRJ_EDITOR];

    $content .= '<TD>' . $forum2perm[$row_dev['sample_flags']] . '</td>';

    $content .= '</TR>';

    return $content;
}
