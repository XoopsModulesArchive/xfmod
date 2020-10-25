<?php
/**
 * SourceForge Generic Tracker facility
 *
 * SourceForge: Breaking Down the Barriers to Open Source Development
 * Copyright 1999-2001 (c) VA Linux Systems
 * http://sourceforge.net
 *
 * @version   $Id: browse.php,v 1.6 2004/04/15 15:25:24 jcox Exp $
 */
$GLOBALS['xoopsOption']['template_main'] = 'tracker/xfmod_browse.html';

//
//  make sure this person has permission to view artifacts
//
if (!$ath->userCanView()) {
    echo _XF_G_PERMISSIONDENIED;

    exit;
}

if (isset($_POST['offset'])) {
    $offset = $_POST['offset'];
} elseif (isset($_GET['offset'])) {
    $offset = $_GET['offset'];
} else {
    $offset = null;
}

if (!isset($offset) || $offset < 0) {
    $offset = 0;
}

if (isset($_POST['set'])) {
    $set = $_POST['set'];
} elseif (isset($_GET['set'])) {
    $set = $_GET['set'];
} else {
    $set = null;
}

if (isset($_POST['order'])) {
    $order = $_POST['order'];
} elseif (isset($_GET['order'])) {
    $order = $_GET['order'];
} else {
    $order = null;
}

if (isset($_POST['_category'])) {
    $_category = $_POST['_category'];
} elseif (isset($_GET['_category'])) {
    $_category = $_GET['_category'];
} else {
    $_category = null;
}

if (isset($_POST['_group'])) {
    $_group = $_POST['_group'];
} elseif (isset($_GET['_group'])) {
    $_group = $_GET['_group'];
} else {
    $_group = null;
}

if (isset($_POST['_status'])) {
    $_status = $_POST['_status'];
} elseif (isset($_GET['_status'])) {
    $_status = $_GET['_status'];
} else {
    $_status = null;
}

if (isset($_POST['_assigned_to'])) {
    $_assigned_to = $_POST['_assigned_to'];
} elseif (isset($_GET['_assigned_to'])) {
    $_assigned_to = $_GET['_assigned_to'];
} else {
    $_assigned_to = null;
}

if (!isset($set)) {
    $_assigned_to = 0;

    $_status = 1;
}
//
//	validate the column names and sort order passed in from user
//	before saving it to prefs
//
if ('artifact_id' == $order || 'summary' == $order || 'open_date' == $order || 'close_date' == $order || 'assigned_to' == $order || 'submitted_by' == $order || 'priority' == $order) {
    $_sort_col = $order;

    if (('ASC' == $sort) || ('DESC' == $sort)) {
        $_sort_ord = $sort;
    } else {
        $_sort_ord = 'ASC';
    }
} else {
    $_sort_col = 'artifact_id';

    $_sort_ord = 'ASC';
}

/*
    Display items based on the form post - by user or status or both
*/

//if status selected, add more to where clause
if ($_status && (100 != $_status)) {
    //for open tasks, add status=100 to make sure we show all

    $status_str = "AND a.status_id='$_status'";
} else {
    //no status was chosen, so don't add it to where clause

    $status_str = '';
}

//if assigned to selected, add to where clause
if ($_assigned_to) {
    $assigned_str = "AND a.assigned_to='$_assigned_to'";
} else {
    //no assigned to was chosen, so don't add it to where clause

    $assigned_str = '';
}

//if category selected, add to where clause
if ($_category && (100 != $_category)) {
    $category_str = "AND a.category_id='$_category'";
} else {
    //no assigned to was chosen, so don't add it to where clause

    $category_str = '';
}

//if artgroup selected, add to where clause
if ($_group && (100 != $_group)) {
    $group_str = "AND a.artifact_group_id='$_group'";
} else {
    //no artgroup to was chosen, so don't add it to where clause

    $group_str = '';
}

//build page title to make bookmarking easier
//if a user was selected, add the user_name to the title
//same for status

//$header = $ath->header();

/**
 *    Build the powerful browsing options pop-up boxes
 */

//
//	creating a custom technician box which includes "any" and "unassigned"
//
$res_tech = $ath->getTechnicians();

$tech_id_arr = util_result_column_to_array($res_tech, 0);
$tech_id_arr[] = '0';  //this will be the 'any' row

$tech_name_arr = util_result_column_to_array($res_tech, 1);
$tech_name_arr[] = 'Any';

$tech_box = html_build_select_box_from_arrays($tech_id_arr, $tech_name_arr, '_assigned_to', $_assigned_to, true, _XF_G_UNASSIGNED);

//
//	custom order by arrays to build a pop-up box
//
$order_name_arr = [];
$order_name_arr[] = _XF_TRK_ID;
$order_name_arr[] = _XF_G_PRIORITY;
$order_name_arr[] = _XF_TRK_SUMMARY;
$order_name_arr[] = _XF_TRK_OPENDATE;
$order_name_arr[] = _XF_TRK_CLOSEDATE;
$order_name_arr[] = _XF_TRK_SUBMITTER;
$order_name_arr[] = _XF_TRK_ASSIGNEE;

$order_arr = [];
$order_arr[] = 'artifact_id';
$order_arr[] = 'priority';
$order_arr[] = 'summary';
$order_arr[] = 'open_date';
$order_arr[] = 'close_date';
$order_arr[] = 'submitted_by';
$order_arr[] = 'assigned_to';

//
//	custom sort arrays to build pop-up box
//
$sort_name_arr = [];
$sort_name_arr[] = _XF_TRK_ASCENDING;
$sort_name_arr[] = _XF_TRK_DESCENDING;

$sort_arr = [];
$sort_arr[] = 'ASC';
$sort_arr[] = 'DESC';

$group = &group_get_object($group_id);

include '../../../header.php';

$header = $ath->header();

//meta tag information
$metaTitle = ' Tracker Browse - ' . $group->getPublicName();
$metaKeywords = project_getmetakeywords($group_id);
$metaDescription = str_replace('"', '&quot;', strip_tags($group->getDescription()));

$xoopsTpl->assign('xoops_pagetitle', $metaTitle);
$xoopsTpl->assign('xoops_meta_keywords', $metaKeywords);
$xoopsTpl->assign('xoops_meta_description', $metaDescription);

//project nav information
$xoopsTpl->assign('project_title', $header['title']);
$xoopsTpl->assign('project_tabs', $header['tabs']);
$xoopsTpl->assign('header', $header['nav']);

//
//	Show the new pop-up boxes to select assigned to, status, etc
//

$content = '
<TABLE WIDTH="10%" BORDER="0">
	<FORM ACTION="' . $_SERVER['PHP_SELF'] . '?group_id=' . $group_id . '&atid=' . $ath->getID() . '" METHOD="POST">
	<INPUT TYPE="HIDDEN" NAME="set" VALUE="custom">
	<TR>
		<TD><FONT SIZE="1">' . _XF_TRK_ASSIGNEE . ':<BR>' . $tech_box . '</TD>' . '<TD><FONT SIZE="1">' . _XF_TRK_STATUS . ':<BR>' . $ath->statusBox('_status', $_status, true, _XF_G_ANY) . '</TD>' . '<TD><FONT SIZE="1">' . _XF_TRK_CATEGORY . ':<BR>' . $ath->categoryBox(
    '_category',
    $_category,
    _XF_G_ANY
) . '</TD>' . '<TD><FONT SIZE="1">' . _XF_TRK_GROUP . ':<BR>' . $ath->artifactGroupBox('_group', $_group, _XF_G_ANY) . '</TD>
	</TR>
	<TR>
		<TD ALIGN="RIGHT"><FONT SIZE="1">' . _XF_TRK_SORTBY . ':</TD>' . '<TD><FONT SIZE="1">' . html_build_select_box_from_arrays($order_arr, $order_name_arr, 'order', $_sort_col, false) . '</TD>' . '<TD><FONT SIZE="1">' . html_build_select_box_from_arrays(
        $sort_arr,
        $sort_name_arr,
        'sort',
        $_sort_ord,
        false
    ) . '</TD>' . '<TD><FONT SIZE="1"><INPUT TYPE="SUBMIT" NAME="SUBMIT" VALUE="' . _XF_G_BROWSE . '"></TD>
	</TR>
	</FORM></TABLE>';

/*
    Show the free-form text submitted by the project admin
*/

$content .= $ts->displayTarea($ath->getBrowseInstructions());

//
//	now run the query using the criteria chosen above
//
$sql = 'SELECT a.priority,a.group_artifact_id,a.artifact_id,a.summary,'
       . 'a.open_date AS date,u.uname AS submitted_by,u2.uname AS assigned_to,ar.resolution_name '
       . 'FROM '
       . $xoopsDB->prefix('xf_artifact')
       . ' a,'
       . $xoopsDB->prefix('xf_artifact_resolution')
       . ' ar,'
       . $xoopsDB->prefix('users')
       . ' u,'
       . $xoopsDB->prefix('users')
       . ' u2 '
       . 'WHERE u.uid=a.submitted_by '
       . "$status_str $assigned_str $category_str $group_str "
       . 'AND u2.uid=a.assigned_to '
       . 'AND a.resolution_id=ar.id '
       . "AND a.group_artifact_id='"
       . $ath->getID()
       . "' "
       . "ORDER BY a.group_artifact_id $_sort_ord, $_sort_col $_sort_ord";

$result = $xoopsDB->query($sql, 51, $offset);

if ($result && $xoopsDB->getRowsNum($result) > 0) {
    if ('custom' == $set) {
        $set .= '&_assigned_to=' . $_assigned_to . '&_status=' . $_status . '&_category=' . $_category . '&_group=' . $_group . '&order=' . $_sort_col . '&sort=' . $_sort_ord;
    }

    $content .= $ath->showBrowseList($result, $offset, $set);

    $content .= vsprintf(_XF_TRK_DENOTESOLDREQUESTS, $ath->getDuePeriod() / 86400);

    $content .= show_priority_colors_key();
} else {
    $content .= '
		<H4>' . _XF_TRK_NOITEMSMATCH . '</H4>';

    $content .= $xoopsDB->error();
}

$xoopsTpl->assign('content', $content);

include '../../../footer.php';
//$ath->footer();
