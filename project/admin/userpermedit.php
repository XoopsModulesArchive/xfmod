<?php
/**
 * Project Admin page to edit permissions for the specific group member
 *
 * This page is linked from userperms.php and from forms to add users
 * to group (located on Project/Foundry Admin main pages).
 *
 * SourceForge: Breaking Down the Barriers to Open Source Development
 * Copyright 1999-2001 (c) VA Linux Systems
 * http://sourceforge.net
 *
 * @version   $Id: userpermedit.php,v 1.14 2004/01/26 18:56:52 devsupaul Exp $
 */
require_once '../../../../mainfile.php';

$langfile = 'project.php';
require_once XOOPS_ROOT_PATH . '/modules/xfmod/include/pre.php';
require_once XOOPS_ROOT_PATH . '/modules/xfmod/project/admin/project_admin_utils.php';
require_once XOOPS_ROOT_PATH . '/modules/xfmod/include/project_summary.php';
require_once XOOPS_ROOT_PATH . '/modules/xfmod/include/tracker/ArtifactType.class';
require_once XOOPS_ROOT_PATH . '/class/xoopsuser.php';
$GLOBALS['xoopsOption']['template_main'] = 'project/admin/xfmod_userpermedit.html';

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

    define('_LOCAL_XF_PRJ_PROJECTROLE', _XF_COMM_COMMROLE);

    define('_LOCAL_XF_PRJ_PROJECTROLE_HELP', _XF_COMM_COMMROLE_HELP);

    define('_LOCAL_XF_PRJ_PROJECTADMIN', _XF_COMM_COMMADMIN);

    define('_LOCAL_XF_PRJ_PROJECTADMIN_HELP', _XF_COMM_COMMADMIN_HELP);
} else {
    define('_LOCAL_XF_G_PROJECT', _XF_G_PROJECT);

    define('_LOCAL_XF_PRJ_PROJECTDEVPERMISSIONS', _XF_PRJ_PROJECTDEVPERMISSIONS);

    define('_LOCAL_XF_PRJ_PROJECTROLE', _XF_PRJ_PROJECTROLE);

    define('_LOCAL_XF_PRJ_PROJECTROLE_HELP', _XF_PRJ_PROJECTROLE_HELP);

    define('_LOCAL_XF_PRJ_PROJECTADMIN', _XF_PRJ_PROJECTADMIN);

    define('_LOCAL_XF_PRJ_PROJECTADMIN_HELP', _XF_PRJ_PROJECTADMIN_HELP);
}

// Builds role selection box with given selected item
function member_role_box($name, $checked)
{
    global $member_roles, $xoopsDB;

    if (!$member_roles) {
        $sql = 'SELECT category_id,name FROM ' . $xoopsDB->prefix('xf_people_job_category');

        $member_roles = $xoopsDB->queryF($sql);
    }

    return html_build_select_box($member_roles, $name, $checked, true, _XF_G_UNDEFINED);
}

// Since there're lot of permissions, and each of them has complex
// HTML rendition (SELECT boxes, etc.), this function is used to reduce
// the background noise.

function render_row($name, $help, $val, $i)
{
    return "<tr class='" . ($i % 2 > 0 ? 'bg1' : 'bg3') . "'><td>" . $name . " <a href=\"javascript:openHelpWindow('" . $help . "')\"><img src='" . XOOPS_URL . "/modules/xfmod/images/help.gif' width='14' height='14' alt='Help'></a></td><td>" . $val . '</td></tr>';
}

// ########################### form submission, make updates

// Netscape allows to submit single-field form by pressing
// Return in the field. In this case, it won't set value for
// submit button
$submit = util_http_track_vars('submit');
$form_unix_name = util_http_track_vars('submit');
if ($submit || $form_unix_name) {
    $func = util_http_track_vars('func');

    $addtotracker = util_http_track_vars('addtotracker');

    if ('adduser' == $func) {
        /*
            We came here from Add User page, need to add user
            and fall thru to show permissions for one
        */

        $res = $xoopsDB->query('SELECT * FROM ' . $xoopsDB->prefix('users') . " WHERE uname='" . $form_unix_name . "'");

        if (!$res || 0 == $xoopsDB->getRowsNum($res)) {
            redirect_header($GLOBALS['HTTP_REFERER'], 1, _XF_PRJ_USERDOESNOTEXSIT);

            exit();
        }

        $u = new XoopsUser($xoopsDB->fetchArray($res));

        if (!$group->addUser($u)) {
            $feedback = 'Error ' . $group->getErrorMessage();
        } else {
            $feedback = ' ' . _XF_PRJ_USERADDEDSUCCESSFULLY . '<br>';
        }

        $user_id = $u->getVar('uid');
    } elseif ($addtotracker) {
        $user_id = util_http_track_vars('user_id');

        $u = new XoopsUser($user_id);

        //

        //  if "add all" option, get list of ArtifactTypes

        //  that this user is not already a member of

        //

        $add_all = util_http_track_vars('add_all');

        if ($add_all) {
            $sql = 'SELECT agl.group_artifact_id '
                   . 'FROM '
                   . $xoopsDB->prefix('xf_artifact_group_list')
                   . ' agl '
                   . 'LEFT JOIN '
                   . $xoopsDB->prefix('xf_artifact_perm')
                   . ' ap ON agl.group_artifact_id=ap.group_artifact_id '
                   . "AND ap.user_id='"
                   . $u->getVar('uid')
                   . "' "
                   . 'WHERE ap.group_artifact_id IS NULL '
                   . "AND agl.group_id='$group_id'";

            $res = $xoopsDB->query($sql);

            $addtoids = util_result_column_to_array($res);
        }

        //

        //	Now take the array of ids and add this user to them

        //

        $count = count($addtoids);

        for ($i = 0; $i < $count; $i++) {
            $ath = new ArtifactType($group, $addtoids[$i]);

            $ath->addUser($u->getVar('uid'));

            if ($ath->isError()) {
                $feedback .= $addtoids[$i] . ': ' . $ath->getErrorMessage();

                $was_error = true;
            }
        }
    } else {
        /*
            Else, we are updating user's permissions
        */

        $u = new XoopsUser($user_id);

        //call to control function in the $Group object

        if ($group->updateUser(
            $u,
            $group_id,
            $admin_flags,
            $forum_flags,
            $project_flags,
            $doc_flags,
            $sample_flags,
            $cvs_flags,
            $release_flags,
            $member_role,
            $artifact_flags
        )) {
            group_add_history(_XF_PRJ_CHANGEDPERMISSIONSFOR, $u->getVar('uname'), $group_id);

            //

            //  Delete the checked ids

            //

            //	keep an assoc array of artifacts this user

            //	was removed from, so we don't then try to update

            //	those artifact type perms in the next step

            $del_arr = [];

            $count = count($deletefrom);

            for ($i = 0; $i < $count; $i++) {
                $del_arr[(string)$deletefrom[$i]] = true;

                $ath = new ArtifactType($group, $deletefrom[$i]);

                $ath->deleteUser($user_id);

                if ($ath->isError()) {
                    $feedback .= $deletefrom[$i] . ': ' . $ath->getErrorMessage();

                    $was_error = true;
                }
            }

            //

            //  Handle the 2-D array of group_artifact_id/permission level

            //

            $count = count($updateperms);

            for ($i = 0; $i < $count; $i++) {
                //

                //	quick check of that assoc array to prevent

                //	updating of perms that don't exist anymore

                //

                if (!$del_arr["$updateperms[$i][0]"]) {
                    $ath = new ArtifactType($group, $updateperms[$i][0]);

                    $ath->updateUser($user_id, $updateperms[$i][1]);

                    if ($ath->isError()) {
                        $feedback .= $updateperms[$i][0] . ': ' . $ath->getErrorMessage();

                        $was_error = true;
                    }
                }
            }

            //if no errors occurred, show just one feedback message

            //instead of the coredump of messages;

            if (!$was_error) {
                $feedback .= ' ' . _XF_PRJ_PERMISSIONSUPDATED . '<br>';
            }
        } else {
            $feedback .= $group->getErrorMessage();
        }
    }
} else {
    //

    //  Set up this user's object

    //

    $u = new XoopsUser($user_id);

    if (!$u || !is_object($u)) {
        $feedback = 'Error creating user object';

        exit;
    }
}

include '../../../../header.php';

$xoopsTpl->assign('project_title', project_title($group));
$xoopsTpl->assign('project_tabs', project_tabs('admin', $group_id));
$xoopsTpl->assign('project_admin_header', get_project_admin_header($group_id, $perm, $group->isProject()));
$content = "
<SCRIPT language=javascript>
<!--
function openHelpWindow(text){
	newWindow = window.open('', 'newWin', 'toolbar=no,location=no,scrollbars=yes,resizable=yes,width=300,height=200')
	newWindow.document.write('<table border=0 width=100% height=100%><tr><td valign=middle>');
	newWindow.document.write(text)
	newWindow.document.write('</td></tr></table>');
	newWindow.document.close()
	newWindow.focus();
}
-->
</SCRIPT>
";
if ($feedback) {
    $content .= "<div class='errorMsg'>" . $feedback . '</div>';
}

// Show description of roles/permissions
$res_dev = $xoopsDB->query(
    'SELECT * ' . 'FROM ' . $xoopsDB->prefix('xf_user_group') . ' ' . "WHERE group_id='$group_id' " . "AND user_id='$user_id'"
);

$content .= '<h2>' . _LOCAL_XF_PRJ_PROJECTDEVPERMISSIONS . ' - ' . $u->getVar('name') . ' (' . $u->getVar('uname') . ')</h2>';

if (!$res_dev || $xoopsDB->getRowsNum($res_dev) < 1) {
    $content .= '<H4>' . _XF_PRJ_DEVELOPERNOTFOUND . '</H4>';

    $content .= $xoopsDB->error();
} else {
    $content .= "<FORM action='" . $_SERVER['PHP_SELF'] . '?group_id=' . $group_id . '&user_id=' . $user_id . "' method='post'>";

    $row_dev = $xoopsDB->fetchArray($res_dev);

    $content .= "
    <table border='0' width='95%' cellpadding='0' cellspacing='0' align='center' valign='top'><tr><td class='bg2'>
    <table border='0' cellpadding='4' cellspacing='1' width='100%'>
    <tr class='bg3' align='left'>
	    <td align='left'><span class='fg2'><b>" . _XF_G_PROPERTY . "</b></span></td>
	    <td align='left'><span class='fg2'><b>" . _XF_G_VALUE . '</b></span></td>
    </tr>';

    $content .= render_row(
        _LOCAL_XF_PRJ_PROJECTROLE,
        _LOCAL_XF_PRJ_PROJECTROLE_HELP,
        member_role_box('member_role', $row_dev['member_role']),
        $i++
    );

    $content .= render_row(
        _LOCAL_XF_PRJ_PROJECTADMIN,
        _LOCAL_XF_PRJ_PROJECTADMIN_HELP,
        html_build_checkbox('admin_flags', 'A', mb_stristr($row_dev['admin_flags'], 'A')),
        $i++
    );

    if ($group->isProject()) {
        if ($group->usesCVS()) {
            $content .= render_row(
                _XF_PRJ_CVSCOMMITRIGHTS,
                _XF_PRJ_CVSCOMMITRIGHTS_HELP,
                html_build_checkbox('cvs_flags', 1, $row_dev['cvs_flags']),
                $i++
            );
        }

        $content .= render_row(
            _XF_PRJ_RELEASEMANAGER,
            _XF_PRJ_RELEASEMANAGER_HELP,
            html_build_select_box_from_arrays(
                [0, 1, 2, 3],
                ['-', _XF_PRJ_TECHNICIAN, _XF_PRJ_ADMINTECH, _XF_G_ADMIN],
                'release_flags',
                $row_dev['release_flags'],
                false
            ),
            $i++
        );

        $content .= render_row(
            _XF_PRJ_PROJECTTASKMANAGER,
            _XF_PRJ_PROJECTTASKMANAGER_HELP,
            html_build_select_box_from_arrays(
                [0, 1, 2, 3],
                ['-', _XF_PRJ_TECHNICIAN, _XF_PRJ_ADMINTECH, _XF_G_ADMIN],
                'project_flags',
                $row_dev['project_flags'],
                false
            ),
            $i++
        );

        $content .= render_row(
            _XF_PRJ_TRACKERMANAGER,
            _XF_PRJ_TRACKERMANAGER_HELP,
            html_build_select_box_from_arrays(
                [0, 2],
                ['-', _XF_G_ADMIN],
                'artifact_flags',
                $row_dev['artifact_flags'],
                false
            ),
            $i++
        );
    }

    $content .= render_row(
        _XF_G_FORUMS,
        _XF_PRJ_FORUMS_HELP,
        html_build_select_box_from_arrays(
            [0, 2],
            ['-', _XF_PRJ_MODERATOR],
            'forum_flags',
            $row_dev['forum_flags'],
            false
        ),
        $i++
    );

    $content .= render_row(
        _XF_PRJ_DOCMANAGER,
        _XF_PRJ_DOCMANAGER_HELP,
        html_build_select_box_from_arrays(
            [0, 1],
            ['-', _XF_PRJ_EDITOR],
            'doc_flags',
            $row_dev['doc_flags'],
            false
        ),
        $i++
    );

    $content .= render_row(
        _XF_PRJ_SAMPLEMANAGER,
        _XF_PRJ_SAMPLEMANAGER_HELP,
        html_build_select_box_from_arrays(
            [0, 1],
            ['-', _XF_PRJ_EDITOR],
            'sample_flags',
            $row_dev['sample_flags'],
            false
        ),
        $i++
    );

    if ($group->isProject()) {
        //

        //	Get the list of permissions that this user has

        //	for ArtifactTypes in this Group

        //

        $res = $xoopsDB->query(
            'SELECT agl.group_artifact_id,agl.name,agl.description,agl.group_id,ap.user_id,ap.perm_level '
            . 'FROM '
            . $xoopsDB->prefix('xf_artifact_perm')
            . ' ap, '
            . $xoopsDB->prefix('xf_artifact_group_list')
            . ' agl '
            . 'WHERE ap.group_artifact_id=agl.group_artifact_id '
            . "AND group_id='$group_id' "
            . "AND user_id='$user_id'"
        );

        $rows = $xoopsDB->getRowsNum($res);

        // Iterate over all trackers of the group

        for ($i = 0; $i < $rows; $i++) {
            $content .= "<INPUT TYPE='HIDDEN' NAME='updateperms["
                        . $i
                        . "][0]' VALUE='"
                        . unofficial_getDBResult($res, $i, 'group_artifact_id')
                        . "'>"
                        . "<TR class='"
                        . ($i % 2 > 0 ? 'bg1' : 'bg3')
                        . "'>"
                        . '<TD>'
                        . unofficial_getDBResult($res, $i, 'name')
                        . " <a href=\"javascript:openHelpWindow('"
                        . _XF_PRJ_TRACKER_HELP
                        . "')\"><img src='"
                        . XOOPS_URL
                        . "/modules/xfmod/images/help.gif' width='14' height='14' alt='Help'></a></TD>"
                        . "<TD><FONT size='-1'><SELECT name='updateperms["
                        . $i
                        . "][1]'>"
                        . "<OPTION value='0'"
                        . (0 == unofficial_getDBResult($res, $i, 'perm_level') ? ' selected' : '')
                        . '>-'
                        . "<OPTION value='1'"
                        . (1 == unofficial_getDBResult($res, $i, 'perm_level') ? ' selected' : '')
                        . '>'
                        . _XF_PRJ_TECHNICIAN
                        . "<OPTION value='2'"
                        . (2 == unofficial_getDBResult($res, $i, 'perm_level') ? ' selected' : '')
                        . '>'
                        . _XF_PRJ_ADMINTECH
                        . "<OPTION value='3'"
                        . (3 == unofficial_getDBResult($res, $i, 'perm_level') ? ' selected' : '')
                        . '>'
                        . _XF_PRJ_ADMINONLY
                        . "</SELECT></FONT> <INPUT TYPE='CHECKBOX' NAME='deletefrom[]' VALUE='"
                        . unofficial_getDBResult($res, $i, 'group_artifact_id')
                        . "'> "
                        . _XF_G_REMOVE
                        . '</TD>'
                        . '</TR>';
        }
    }

    $content .= "
	<TR class='bg3'><TD COLSPAN='2'><p align='center'>
		<INPUT type='submit' name='submit' value='" . _XF_PRJ_UPDATEDEVELOPERPERMISSIONS . "'>
		<INPUT type='reset' value='" . _XF_G_CANCEL . "'>
		</FORM>
	</TD></TR>

	</TABLE></TD></TR></TABLE>";

    if ($group->isProject()) {
        $content .= '
		<P>
		<H4>' . _XF_PRJ_ADDUSERSTHESETRACKERS . ':</H4>
		<P>
		' . _XF_PRJ_ADDUSERSTHESETRACKERSINFO . "
		<P>
		<CENTER>
		<FORM action='" . $_SERVER['PHP_SELF'] . '?group_id=' . $group_id . '&user_id=' . $user_id . "' method='post'>
		<INPUT TYPE='HIDDEN' NAME='addtotracker' VALUE='y'>";

        $sql = 'SELECT agl.group_artifact_id,agl.name '
               . 'FROM '
               . $xoopsDB->prefix('xf_artifact_group_list')
               . ' agl '
               . 'LEFT JOIN '
               . $xoopsDB->prefix('xf_artifact_perm')
               . ' ap ON agl.group_artifact_id=ap.group_artifact_id '
               . "AND ap.user_id='"
               . $u->uid()
               . "' "
               . 'WHERE ap.group_artifact_id IS NULL '
               . "AND agl.group_id='$group_id'";

        $res = $xoopsDB->query($sql);

        $content .= $xoopsDB->error();

        $content .= html_build_multiple_select_box($res, 'addtoids[]', [], 8, false);

        $content .= '<P>';

        $content .= "<INPUT type='submit' name='submit' value='" . _XF_PRJ_ADDTOTRACKER . "'>&nbsp;" . "<INPUT type='checkbox' name='add_all' value='1'> " . _XF_PRJ_ADDTOALL . '</FORM>' . '</CENTER>';
    }
}

$xoopsTpl->assign('content', $content);
include '../../../../footer.php';
