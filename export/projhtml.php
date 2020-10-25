<?php

/**
 * SourceForge Exports: Export project summary page as HTML
 *
 * SourceForge: Breaking Down the Barriers to Open Source Development
 * Copyright 1999-2001 (c) VA Linux Systems
 * http://sourceforge.net
 *
 * @version   $Id: projhtml.php,v 1.2 2004/10/09 23:57:20 praedator Exp $
 */
require_once '../../../mainfile.php';

$langfile = 'project.php';
require_once XOOPS_ROOT_PATH . '/modules/xfmod/include/pre.php';
require_once XOOPS_ROOT_PATH . '/modules/xfmod/include/vote_function.php';
require_once XOOPS_ROOT_PATH . '/modules/xfmod/include/vars.php';
require_once XOOPS_ROOT_PATH . '/modules/xfmod/news/news_utils.php';
require_once XOOPS_ROOT_PATH . '/modules/xfmod/include/trove.php';
require_once XOOPS_ROOT_PATH . '/modules/xfmod/include/project_summary.php';
require_once XOOPS_ROOT_PATH . '/themes/' . getTheme() . '/forge.php';

$res = $xoopsDB->query('SELECT group_id, unix_group_name FROM ' . $xoopsDB->prefix('xf_groups') . " WHERE group_id='" . $group_id . "'");
if (!$res || $xoopsDB->getRowsNum($res) < 1) {
} else {
    $group_arr = $xoopsDB->fetchArray($res);

    $group_id = $group_arr['group_id'];

    $group_name = $group_arr['unix_group_name'];
}

$project = &group_get_object($group_id);

if (!$project) {
    redirect_header(XOOPS_URL, 4, _XF_PRJ_PROJECTDOESNOTEXIST);

    exit;
}
$perm = &$project->getPermission($xoopsUser);

//group is private
if (!$project->isPublic()) {
    //if it's a private group, you must be a member of that group

    if (!$project->isMemberOfGroup($xoopsUser) && !$perm->isSuperUser()) {
        redirect_header(XOOPS_URL . '/', 4, _XF_PRJ_PROJECTMARKEDASPRIVATE);

        exit;
    }
}

//for dead projects must be member of xoopsforge project
if (!$project->isActive() && !$perm->isSuperUser()) {
    redirect_header(XOOPS_URL, 4, _XF_PRJ_NOTAUTHORIZEDTOENTER);

    exit;
}

$title = _XF_PRJ_PUBLICAREA;
$content = '';
// ################# Homepage Link

$content .= "<A href='" . $project->getHomePage() . "'>";
$content .= "<img src='" . XOOPS_URL . "/modules/xfmod/images/ic/home16b.png' height='20' width='20' alt='" . _XF_G_HOMEPAGE . "'>";
$content .= '&nbsp;' . _XF_PRJ_PROJECTHOMEPAGE . '</A>';

// ################## ArtifactTypes

$content .= "<HR SIZE='1' noshade><A href='" . XOOPS_URL . '/modules/xfmod/tracker/?group_id=' . $group_id . "'>";
$content .= "<img src='" . XOOPS_URL . "/modules/xfmod/images/ic/taskman16b.png' height='20' width='20' alt='" . _XF_PRJ_PROJECTTRACKERS . "'>";
$content .= ' ' . _XF_G_TRACKERS . '</A>';

$result = $xoopsDB->query(
    'SELECT agl.*,aca.count,aca.open_count '
    . 'FROM '
    . $xoopsDB->prefix('xf_artifact_group_list')
    . ' agl '
    . 'LEFT JOIN '
    . $xoopsDB->prefix('xf_artifact_counts_agg')
    . ' aca USING (group_artifact_id) '
    . "WHERE agl.group_id='$group_id' "
    . 'AND agl.is_public=1 '
    . 'ORDER BY group_artifact_id ASC'
);

$rows = $xoopsDB->getRowsNum($result);

if (!$result || $rows < 1) {
    $content .= '<BR><I>' . _XF_PRJ_NOPUBLICTRACKERS . '</I>';
} else {
    for ($j = 0; $j < $rows; $j++) {
        $content .= '<P>'
                    . "&nbsp;-&nbsp;<A HREF='"
                    . XOOPS_URL
                    . '/modules/xfmod/tracker/?atid='
                    . unofficial_getDBResult($result, $j, 'group_artifact_id')
                    . '&group_id='
                    . $group_id
                    . "&func=browse'>"
                    . $ts->htmlSpecialChars(unofficial_getDBResult($result, $j, 'name'))
                    . '</A>'
                    . ' ( <B>'
                    . unofficial_getDBResult($result, $j, 'open_count')
                    . ' '
                    . _XF_PRJ_OPEN
                    . ' / '
                    . unofficial_getDBResult($result, $j, 'count')
                    . ' '
                    . _XF_PRJ_TOTAL
                    . '</B> )<BR>'
                    . $ts->htmlSpecialChars(unofficial_getDBResult($result, $j, 'description'));
    }
}

// ################## forums

if ($project->usesForum()) {
    $content .= "<HR SIZE='1' NoShade><A href='" . XOOPS_URL . '/modules/xfmod/forum/?group_id=' . $group_id . "'>";

    $content .= "<img src='" . XOOPS_URL . "/modules/xfmod/images/ic/notes16.png' width='20' height='20' alt='" . _XF_G_FORUMS . "'>";

    $content .= '&nbsp;' . _XF_PRJ_PUBLICFORUMS . '</A>';

    $content .= ' ( ' . sprintf(_XF_PRJ_MESSAGESINFORUMS, '<B>' . project_get_public_forum_message_count($group_id) . '</B>', '<B>' . project_get_public_forum_count($group_id) . '</B>') . " )\n";
}

// ##################### Doc Manager

if ($project->usesDocman()) {
    $content .= "<HR SIZE='1' NoShade>";

    $content .= "<A href='" . XOOPS_URL . '/modules/xfmod/docman/?group_id=' . $group_id . "'>";

    $content .= "<img src='" . XOOPS_URL . "/modules/xfmod/images/ic/docman16b.png' width='20' height='20' alt='" . _XF_G_DOCS . "'>";

    $content .= '&nbsp;' . _XF_PRJ_PROJECTDOCUMENTATION . '</A>';
}

// ##################### Task Manager

if ($project->usesPm()) {
    $content .= "<HR SIZE='1' NoShade><A href='" . XOOPS_URL . '/modules/xfmod/pm/?group_id=' . $group_id . "'>";

    $content .= "<img src='" . XOOPS_URL . "/modules/xfmod/images/ic/taskman16b.png' width='20' height='20' alt='" . _XF_G_TASKS . "'>";

    $content .= '&nbsp;' . _XF_PRJ_TASKMANAGER . '</A>';

    $sql = 'SELECT * FROM ' . $xoopsDB->prefix('xf_project_group_list') . " WHERE group_id='$group_id' AND is_public=1";

    $result = $xoopsDB->query($sql);

    $rows = $xoopsDB->getRowsNum($result);

    if (!$result || $rows < 1) {
        $content .= '<BR><I>' . _XF_PRJ_NOSUBPROJECTS . '</I>';
    } else {
        for ($j = 0; $j < $rows; $j++) {
            $content .= "<BR> &nbsp; - <A HREF='"
                        . XOOPS_URL
                        . '/modules/xfmod/pm/task.php?group_project_id='
                        . unofficial_getDBResult($result, $j, 'group_project_id')
                        . '&group_id='
                        . $group_id
                        . "&func=browse'>"
                        . $ts->htmlSpecialChars(unofficial_getDBResult($result, $j, 'project_name'))
                        . '</A>';
        }
    }
}

// ######################### Surveys

if ($project->usesSurvey()) {
    $content .= "<HR SIZE='1' NoShade><A href='" . XOOPS_URL . '/modules/xfmod/survey/?group_id=' . $group_id . "'>";

    $content .= "<img src='" . XOOPS_URL . "/modules/xfmod/images/ic/survey16b.png' width='20' height='20' alt='" . _XF_G_SURVEYS . "'>";

    $content .= ' ' . _XF_G_SURVEYS . '</A>';

    $content .= ' ( <B>' . project_get_survey_count($group_id) . '</B> ' . _XF_PRJ_SURVEYS . ' )';
}

$content .= "<center><BR><a href='" . XOOPS_URL . '/modules/xfmod/project/?' . $group_name . "' target='_blank'><img src='" . XOOPS_URL . "/modules/xfmod/images/xflogo-155-1.gif'></a></center>";

themesidebox($title, $content);

//echo project_summary($group_id,$mode,$no_table);

//echo $content;
