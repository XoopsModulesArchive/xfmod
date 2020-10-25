<?php
/**
 * SourceForge Trackerss Facility
 *
 * SourceForge: Breaking Down the Barriers to Open Source Development
 * Copyright 1999-2001 (c) VA Linux Systems
 * http://sourceforge.net
 *
 * @version   $Id: monitor.php,v 1.1 2005/02/21 14:56:33 mercibe Exp $
 */
require_once '../../../mainfile.php';

$langfile = 'tracker.php';
$langfile = 'tracker.php';
require_once XOOPS_ROOT_PATH . '/modules/xfmod/include/pre.php';
require_once XOOPS_ROOT_PATH . '/modules/xfmod/include/project_summary.php';

if ($xoopsUser) {
    /*
        User obviously has to be logged in to monitor trackers's activities
    */

    if ($group_id) {
        $result = $xoopsDB->queryF(
            'SELECT group_id FROM ' . ' ' . $xoopsDB->prefix('xf_groups') . " WHERE group_id='$group_id'"
        );

        // The project MUST exist !

        if (!$result || $xoopsDB->getRowsNum($result) < 1) {
            redirect_header($GLOBALS['HTTP_REFERER'], 4, 'ERROR<br>' . _XF_TRK_NOPROJECTFOUND . ' ' . $xoopsDB->error());

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

        if ($notificationHandler->isSubscribed('trackers', $atid, 'new_post', $xoopsModule->getVar('mid'), $xoopsUser->getVar('uid'))) {
            /*
                User is monitoring this tracker => stop monitoring
            */

            $notificationHandler->unsubscribe('trackers', $atid, 'new_post', $xoopsModule->getVar('mid'), $xoopsUser->getVar('uid'));

            redirect_header(XOOPS_URL . "/modules/xfmod/tracker/?group_id=$group_id&atid=$atid", 2, _XF_TRK_TRKISNOTMONITORED);

            exit;
        }  

        /*
            User is not already monitoring this tracker => monitoring can begin
        */

        $notificationHandler->subscribe('trackers', $atid, 'new_post', null, $xoopsModule->getVar('mid'), $xoopsUser->getVar('uid'));

        redirect_header(XOOPS_URL . "/modules/xfmod/tracker/?group_id=$group_id&atid=$atid", 2, _XF_TRK_TRKISMONITORED);

        exit;
    }
} else {
    redirect_header($GLOBALS['HTTP_REFERER'], 2, _XF_G_PERMISSIONDENIED . '<br>' . _XF_FRM_MUSTLOGGEDINTOMONITOR);

    exit;
}
