<?php
// $Id: forum_new.php,v 1.2 2004/10/09 23:57:19 praedator Exp $
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
// Author: Kazumi Ono (AKA onokazu)                                          //
// URL: http://www.myweb.ne.jp/, https://www.xoops.org/, http://jp.xoops.org/ //
// Project: The XOOPS Project                                                //
// ------------------------------------------------------------------------- //
// Recent private forums block (Bloc Forum privé©                            //
// Author: L'éñuipe de TheNetSpace ( http://www.thenetspace.com )            //
// ------------------------------------------------------------------------- //

function b_forum_new_show($options)
{
    $db = XoopsDatabaseFactory::getDatabaseConnection();

    $myts = MyTextSanitizer::getInstance();

    $block = [];

    $forum_id = 1;

    if (!$max_rows || $max_rows < 5) {
        $max_rows = 5;
    }

    $sql = 'SELECT f.most_recent_date,u.uname,u.name,u.uid,f.msg_id,f.group_forum_id,f.subject,f.thread_id,g.group_name,g.group_id, '
           . '(COUNT(f2.thread_id)-1) AS followups, MAX(f2.date) AS recent '
           . 'FROM '
           . $db->prefix('xf_forum')
           . ' f, '
           . $db->prefix('xf_forum')
           . ' f2, '
           . $db->prefix(
               'users'
           )
           . ' u, '
           . $db->prefix('xf_forum_group_list')
           . ' t, '
           . $db->prefix('xf_groups')
           . ' g  '
           . 'WHERE t.group_forum_id=f.group_forum_id '
           . 'AND g.group_id=t.group_id '
           . 'AND f.is_followup_to=0 '
           . 'AND u.uid=f.posted_by '
           . 'AND f.thread_id=f2.thread_id '
           . 'GROUP BY f.most_recent_date,u.uname,u.name,u.uid,f.msg_id,f.subject,f.thread_id '
           . 'ORDER BY f.most_recent_date DESC';

    $result = $db->query($sql, ($max_rows + 1), $offset);

    if (0 != $options[1]) {
        $block['full_view'] = true;
    } else {
        $block['full_view'] = false;
    }

    $block['lang_forum'] = _XF_BLFO_FORUM;

    $block['lang_topic'] = _XF_BLFO_TOPIC;

    $block['lang_replies'] = _XF_BLFO_RPLS;

    $block['lang_lastpost'] = _XF_BLFO_LPOST;

    $i = 0;

    while (($row = $db->fetchArray($result)) && ($i < $max_rows)) {
        $topic['group_id'] = $row['group_id'];

        $topic['forum_id'] = $row['group_forum_id'];

        $topic['forum_name'] = htmlspecialchars($row['group_name'], ENT_QUOTES | ENT_HTML5);

        $topic['id'] = $row['thread_id'];

        $topic['title'] = htmlspecialchars($row['subject'], ENT_QUOTES | ENT_HTML5);

        $topic['replies'] = $row['followups'];

        $topic['post_id'] = $row['msg_id'];

        $topic['time'] = formatTimestamp($row['recent'], 'm') . '<br><b>' . $row['uname'] . '</b>';

        $block['topics'][] = &$topic;

        unset($topic);

        $i++;
    }

    return $block;
}

function b_forum_new_edit($options)
{
    $inputtag = "<input type='text' name='options[0]' value='" . $options[0] . "'>";

    $form = sprintf(_XF_BLFO_DISPLAY, $inputtag);

    $form .= '<br>' . _XF_BLFO_DISPLAYF . "&nbsp;<input type='radio' name='options[1]' value='1'";

    if (1 == $options[1]) {
        $form .= ' checked';
    }

    $form .= '>&nbsp;' . _YES . "<input type='radio' name='options[1]' value='0'";

    if (0 == $options[1]) {
        $form .= ' checked';
    }

    $form .= '>&nbsp;' . _NO;

    $form .= '<input type="hidden" name="options[2]" value="' . $options[2] . '">';

    return $form;
}
