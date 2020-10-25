<?php
/**
 * SourceForge Forums Facility
 *
 * SourceForge: Breaking Down the Barriers to Open Source Development
 * Copyright 1999-2001 (c) VA Linux Systems
 * http://sourceforge.net
 *
 * @version   $Id: monitor.php,v 1.1 2005/02/18 14:21:17 mercibe Exp $
 */
require_once '../../../mainfile.php';

$langfile = 'news.php';
require_once XOOPS_ROOT_PATH . '/modules/xfmod/include/pre.php';
require_once XOOPS_ROOT_PATH . '/modules/xfmod/include/project_summary.php';
require_once XOOPS_ROOT_PATH . '/modules/xfmod/news/news_utils.php';

if ($xoopsUser) {
    /*
        User obviously has to be logged in to monitor  project News
    */

    if ($group_id) {
        $result = $xoopsDB->queryF(
            'SELECT group_id FROM ' . ' ' . $xoopsDB->prefix('xf_groups') . " WHERE group_id='$group_id'"
        );

        if (!$result || $xoopsDB->getRowsNum($result) < 1) {
            redirect_header($GLOBALS['HTTP_REFERER'], 4, 'ERROR<br>' . _XF_NWS_NOPROJECTFOUND . ' ' . $xoopsDB->error());

            exit;
        }

        $group = &group_get_object($group_id);

        $perm = &$group->getPermission($xoopsUser);

        //group is private

        if (!$group->isPublic()) {
            //if it's a private group, you must be a member of that group

            if (!$group->isMemberOfGroup($xoopsUser) && !$perm->isSuperUser()) {
                redirect_header(XOOPS_URL . '/', 4, _XF_PRJ_PROJECTMARKEDASPRIVATE);

                exit;
            }
        }

        /*
            Check to see if they are already monitoring
            this project. If they are, stop monitoring.
            If they are NOT, start monitoring
        */

        $xoopsModule = XoopsModule::getByDirname('xfmod');

        $notificationHandler = xoops_getHandler('notification');

        if ($notificationHandler->isSubscribed('news', $group_id, 'new_post', $xoopsModule->getVar('mid'), $xoopsUser->getVar('uid'))) {
            /*
                User is thread => stop monitoring
            */

            $notificationHandler->unsubscribe('news', $group_id, 'new_post', $xoopsModule->getVar('mid'), $xoopsUser->getVar('uid'));

            redirect_header(XOOPS_URL . "/modules/xfmod/news/?group_id=$group_id", 2, _XF_NEWS_NEWSARENOTMONITORED);

            exit;
        }  

        /*
            User is not already monitoring thread => monitoring can begin
        */

        $notificationHandler->subscribe('news', $group_id, 'new_post', null, $xoopsModule->getVar('mid'), $xoopsUser->getVar('uid'));

        redirect_header(XOOPS_URL . "/modules/xfmod/news/?group_id=$group_id", 2, _XF_NEWS_NEWSAREMONITORED);

        exit;
    }
} else {
    redirect_header($GLOBALS['HTTP_REFERER'], 2, _XF_G_PERMISSIONDENIED . '<br>' . _XF_FRM_MUSTLOGGEDINTOMONITOR);

    exit;
}
