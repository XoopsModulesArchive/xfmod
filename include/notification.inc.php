<?php
// $Id: notification.inc.php,v 1.2 2005/02/21 14:56:31 mercibe Exp $
//  ------------------------------------------------------------------------ //
//                XOOPS - PHP Content Management System                      //
//                    Copyright (c) 2000 XOOPS.org                           //
//                       <https://www.xoops.org>                             //
//  ------------------------------------------------------------------------ //
//  This program is free software; you can redistribute it and/or modify     //
//  it under the terms of the GNU General Public License as published by     //
//  the Free Software Foundation; either version 2 of the License, or        //
//  (at your option) any later version.                                      //
//                                                                           //
//  You may not change or alter any portion of this comment or credits       //
//  of supporting developers from this source code or any supporting         //
//  source code which is considered copyrighted (c) material of the          //
//  original comment or credit authors.                                      //
//                                                                           //
//  This program is distributed in the hope that it will be useful,          //
//  but WITHOUT ANY WARRANTY; without even the implied warranty of           //
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the            //
//  GNU General Public License for more details.                             //
//                                                                           //
//  You should have received a copy of the GNU General Public License        //
//  along with this program; if not, write to the Free Software              //
//  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307 USA //
//  ------------------------------------------------------------------------ //

function forge_notify_iteminfo($category, $item_id)
{
    $moduleHandler = xoops_getHandler('module');

    $module = $moduleHandler->getByDirname('xfmod');

    if ('global' == $category) {
        $item['name'] = '';

        $item['url'] = '';

        return $item;
    }

    global $xoopsDB;

    if ('news' == $category) {
        // Assume we have a valid group_id

        $sql = 'SELECT group_name FROM ' . $xoopsDB->prefix('xf_groups') . ' WHERE group_id = ' . $item_id;

        $result = $xoopsDB->query($sql); // TODO: error check

        $result_array = $xoopsDB->fetchArray($result);

        $item['name'] = $result_array['group_name'];

        $item['url'] = XOOPS_URL . '/modules/' . $module->getVar('dirname') . '/news/?group_id=' . $item_id;

        return $item;
    }

    if ('trackers' == $category) {
        // Assume we have a valid group_id

        /*
        SELECT a.group_artifact_id, a.name, g.group_id, g.group_name
                FROM xoops_xf_artifact_group_list a, xoops_xf_groups g
                WHERE a.group_id = g.group_id AND group_artifact_id = XYZ
        */

        $sql = 'SELECT a.group_artifact_id, a.name, g.group_id, g.group_name FROM ' . $xoopsDB->prefix('xf_artifact_group_list') . ' a, ' . $xoopsDB->prefix('xf_groups') . ' g WHERE a.group_id = g.group_id AND group_artifact_id = ' . $item_id;

        $result = $xoopsDB->query($sql); // TODO: error check

        $result_array = $xoopsDB->fetchArray($result);

        $item['name'] = $result_array['group_name'] . ' :: ' . $result_array['name'];

        $item['url'] = XOOPS_URL . '/modules/' . $module->getVar('dirname') . '/tracker/?atid=' . $item_id . '&group_id=' . $result_array['group_id'];

        return $item;
    }
}
