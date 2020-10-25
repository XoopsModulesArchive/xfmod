<?php
/**
 * SourceForge Mailing List Manager
 *
 * SourceForge: Breaking Down the Barriers to Open Source Development
 * Copyright 1999-2001 (c) VA Linux Systems
 * http://sourceforge.net
 *
 * @version   $Id: index.php,v 1.11 2004/01/30 18:05:00 jcox Exp $
 */

/*
        by Quentin Cregan, SourceForge 06/2000
*/
require_once '../../../mainfile.php';

$langfile = 'maillist.php';
require_once XOOPS_ROOT_PATH . '/modules/xfmod/include/pre.php';
require_once XOOPS_ROOT_PATH . '/modules/xfmod/maillist/maillist_utils.php';
require_once XOOPS_ROOT_PATH . '/modules/xfmod/include/project_summary.php';

if ($group_id) {
    $project = &group_get_object($group_id);

    $perm = &$project->getPermission($xoopsUser);

    //group is private

    if (!$project->isPublic()) {
        //if it's a private group, you must be a member of that group

        if (!$project->isMemberOfGroup($xoopsUser) && !$perm->isSuperUser()) {
            redirect_header(XOOPS_URL . '/', 4, _XF_PRJ_PROJECTMARKEDASPRIVATE);

            exit;
        }
    }

    if ($project->isFoundry()) {
        define('_LOCAL_XF_ML_MLNOTENABLED', _XF_ML_MLNOTENABLECOMM);

        define('_LOCAL_XF_G_PROJECT', _XF_G_COMM);

        define('_LOCAL_XF_ML_FULLNAME', _XF_ML_FULLNAMECOMM);
    } else {
        define('_LOCAL_XF_ML_MLNOTENABLED', _XF_ML_MLNOTENABLED);

        define('_LOCAL_XF_G_PROJECT', _XF_G_PROJECT);

        define('_LOCAL_XF_ML_FULLNAME', _XF_ML_FULLNAME);
    }

    if (!$project->usesMail()) {
        redirect_header($GLOBALS['HTTP_REFERER'], 4, _LOCAL_XF_ML_MLNOTENABLED);

        exit;
    }

    include '../../../header.php';

    //meta tag information

    $metaTitle = ': ' . _XF_ML_LISTS . ' - ' . $project->getPublicName();

    $metaKeywords = project_getmetakeywords($group_id);

    $metaDescription = str_replace('"', '&quot;', strip_tags($project->getDescription()));

    $xoopsTpl->assign('xoops_pagetitle', $metaTitle);

    $xoopsTpl->assign('xoops_meta_keywords', $metaKeywords);

    $xoopsTpl->assign('xoops_meta_description', $metaDescription);

    //project nav information

    echo project_title($project);

    echo project_tabs('maillist', $group_id);

    if ($perm->isAdmin() || $perm->isSuperUser()) {
        // Provide administrative link to site admins or superusers.

        echo "<p><b><a href='" . XOOPS_URL . "/modules/xfmod/maillist/admin/index.php?group_id=$group_id'>" . _XF_G_ADMIN . '</a></b></p>';
    }

    echo "<p>\n";

    $sql = 'SELECT name, description FROM ' . $xoopsDB->prefix('xf_maillists') . " WHERE group_id=$group_id";

    $result = $xoopsDB->query($sql);

    $rows = $xoopsDB->getRowsNum($result);

    if (!$result || $rows < 1) {
        echo '<b>No mailing lists found for ' . $project->getPublicName() . '</b>';
    } else {
        while (list($suffix, $desc) = $xoopsDB->fetchRow($result)) {
            echo '<b>' . $project->getUnixName() . '-' . $suffix . '</b> - ' . $desc . "<br>\n&nbsp;&nbsp;";

            echo '<a href="'
                 . XOOPS_URL
                 . '/modules/xfmod/maillist/subscribe.php?group_id='
                 . $group_id
                 . '&list='
                 . urlencode($project->getUnixName() . '-' . $suffix)
                 . '">'
                 . _XF_ML_SUBSCRIBE
                 . "</a>\n"
                 . '&nbsp; | &nbsp; <a href="http://'
                 . $_SERVER['SERVER_NAME']
                 . '/modules/xfmod/maillist/archbrowse.php/'
                 . $project->getUnixName()
                 . '-'
                 . $suffix
                 . '/?id='
                 . $group_id
                 . '&prjname='
                 . $project->getUnixName()
                 . '&mlname='
                 . $suffix
                 . '">'
                 . _XF_ML_VIEW_ARCHIVE
                 . "</a><br><br>\n";
        }
    }

    include '../../../footer.php';
} else {
    redirect_header($GLOBALS['HTTP_REFERER'], 4, 'Error<br>No Group');

    exit;
}
