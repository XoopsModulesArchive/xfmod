<?php

require_once XOOPS_ROOT_PATH . '/class/xoopsformloader.php';

function b_xfmod_show($options)
{
    global $xoopsDB;

    $ts = MyTextSanitizer::getInstance();

    $block = [];

    $block['title'] = _MB_XFMOD_LATEST_NEWS;

    $block['content'] = '';

    $limit = $options[0];

    $show_summaries = (1 == $options[1] ? true : false);

    $flat = (1 == $options[2] ? true : false);

    $tail_headlines = $options[3];

    $sql = 'SELECT g.group_id,g.unix_group_name,g.group_name,g.type,u.uid,u.uname,nb.forum_id,nb.summary,nb.date,nb.details '
           . 'FROM '
           . $xoopsDB->prefix('users')
           . ' u,'
           . $xoopsDB->prefix('xf_news_bytes')
           . ' nb,'
           . $xoopsDB->prefix('xf_groups')
           . ' g '
           . 'WHERE nb.is_approved=1 '
           . 'AND u.uid=nb.submitted_by '
           . 'AND nb.group_id=g.group_id '
           . 'ORDER BY date DESC';

    $result = $xoopsDB->query($sql, $limit + $tail_headlines);

    $rows = $xoopsDB->getRowsNum($result);

    if (!$result || $rows < 1) {
        $block['content'] .= _MB_XFMOD_NO_FOUND;

        $block['content'] .= $xoopsDB->error();
    } else {
        $block['content'] .= '<DL COMPACT>';

        for ($i = 0; $i < $rows; $i++) {
            $item = $xoopsDB->fetchArray($result);

            if ($show_summaries && $limit) {
                //get the first paragraph of the story

                $arr = explode("\n", $item['details']);

                //if the first paragraph is short, and so are following paragraphs, add the next paragraph on

                if ((mb_strlen($arr[0]) < 200) && (mb_strlen($arr[1] . $arr[2]) < 300) && (mb_strlen($arr[2]) > 5)) {
                    $summ_txt = '<br>' . $arr[0] . '<br>' . $arr[1] . '<br>' . $arr[2];
                } else {
                    $summ_txt = '<br>' . $arr[0];
                }

                //show the project name

                if (2 == $type) {
                    $group_type = '/foundry';
                } else {
                    $group_type = '/project';
                }

                $proj_name = " &nbsp; - &nbsp; <A HREF='" . XOOPS_URL . '/xf' . $group_type . '/?' . $item['unix_group_name'] . "'>" . $item['group_name'] . '</A>';
            } else {
                $proj_name = '';

                $summ_txt = '';
            }

            if (!$limit) {
                $block['content'] .= "<li><A HREF='" . XOOPS_URL . '/modules/xfmod/forum/forum.php?forum_id=' . $item['forum_id'] . "'><B>" . $ts->displayTarea($item['summary']) . '</B></A>';

                $block['content'] .= ' &nbsp; <I>' . date(_SHORTDATESTRING, $date) . '</I><br>';
            } else {
                $block['content'] .= "<A HREF='" . XOOPS_URL . '/modules/xfmod/forum/forum.php?forum_id=' . $item['forum_id'] . "'><B>" . $ts->displayTarea($item['summary']) . '</B></A>';

                if (!$flat) {
                    $block['content'] .= '<br>&nbsp;';
                }

                $block['content'] .= "&nbsp;&nbsp;&nbsp;<I><a href='" . XOOPS_URL . '/userinfo.php?uid=' . $item['uid'] . "'>" . $item['uname'] . '</a> - ' . date(_SHORTDATESTRING, $item['date']) . '</I>' . $proj_name . '<BR>' . $ts->displayTarea($summ_txt);

                $sql = 'SELECT COUNT(f.msg_id) AS count '
                       . ' FROM '
                       . $xoopsDB->prefix('xf_forum')
                       . ' f'
                       . ','
                       . $xoopsDB->prefix('xf_forum_group_list')
                       . ' fgl'
                       . ','
                       . $xoopsDB->prefix('xf_config')
                       . ' c'
                       . ' WHERE f.group_forum_id=fgl.group_forum_id'
                       . ' AND fgl.is_public=1 '
                       . " AND c.name='sysnews'"
                       . ' AND fgl.group_id=c.value'
                       . ' AND f.group_forum_id='
                       . $item['forum_id'];

                $res2 = $xoopsDB->query($sql);

                [$num_comments] = $xoopsDB->fetchRow($res2);

                $block['content'] .= "<div align='center'> [ <A HREF='" . XOOPS_URL . '/modules/xfmod/forum/forum.php?forum_id=' . $item['forum_id'] . "'>";

                if ($num_comments) {
                    $block['content'] .= $num_comments . ' ';

                    $block['content'] .= (1 == $num_comments) ? _MB_XFMOD_COMMENT : _MB_XFMOD_COMMENTS;
                } else {
                    $block['content'] .= _MB_XFMOD_READ . '/' . _MB_XFMOD_COMMENT;
                }

                $block['content'] .= "</a> ]</div><HR width='100%' size='1' noshade>";
            }

            if (1 == $limit && $tail_headlines) {
                $block['content'] .= '<ul>';
            }

            if ($limit) {
                $limit--;
            }
        }
    }

    if ($tail_headlines) {
        $block['content'] .= "</ul><HR width='100%' size='1' noshade>\n";
    }

    $block['content'] .= "<div align='center'>" . "[ <a href='" . XOOPS_URL . "/modules/xfmod/news/'>" . _MB_XFMOD_NEWS_ARCHIVE . '</a> ]</div>';

    return $block;
}

function b_xfmod_edit($options)
{
    $summaries = new XoopsFormRadioYN(_MB_XFMOD_SHOW_SUMM, 'options[1]', $options[1]);

    $flat = new XoopsFormRadioYN(_MB_XFMOD_SHOW_FLAT, 'options[2]', $options[2]);

    $form = _MB_XFMOD_LIMIT_HEAD . "&nbsp;<input type='text' name='options[]' value='" . $options[0] . "'>&nbsp;" . _MB_XFMOD_ARTICLES . '<br>';

    $form .= _MB_XFMOD_SHOW_SUMM . '&nbsp;' . $summaries->render() . '<br>';

    $form .= _MB_XFMOD_SHOW_FLAT . '&nbsp;' . $flat->render() . '<br>';

    $form .= _MB_XFMOD_TAIL_HEAD . "&nbsp;<input type='text' name='options[]' value='" . $options[3] . "'>&nbsp;" . _MB_XFMOD_ARTICLES;

    return $form;
}

function b_xfmod_communities()
{
    global $xoopsDB;

    $block = [];

    $block['title'] = _MB_XFMOD_COMM;

    $block['content'] = '<table border=0 cellpadding=0 cellspacing=0>';

    $sql = 'SELECT group_name, unix_group_name FROM ' . $xoopsDB->prefix('xf_groups') . " WHERE type=2 AND is_public=1 AND status='A'";

    $result = $xoopsDB->query($sql);

    $rows = $xoopsDB->getRowsNum($result);

    if (!$result || $rows < 1) {
        $block['content'] .= '<tr><td>' . _MB_XFMOD_NOCOMM . '</td></tr>';
    } else {
        for ($i = 0; $i < $rows; $i++) {
            $curr_group = $xoopsDB->fetchArray($result);

            $block['content'] .= "<tr><td valign='top'><img src='" . XOOPS_URL . "/modules/xfmod/images/n_arrows_grey.gif' width='7' height='7' border='0' alt=''>&nbsp;</td><td><a href='" . XOOPS_URL . '/modules/xfmod/community/?' . $curr_group['unix_group_name'] . "'>";

            $block['content'] .= $curr_group['group_name'] . '</a></td></tr>';
        }
    }

    $block['content'] .= '</table>';

    return $block;
}

function b_xfmod_pending_items()
{
    global $xoopsDB, $xoopsUser;

    if (!$xoopsUser) {
        return [];
    }

    //$xoopsDB = XoopsDatabaseFactory::getDatabaseConnection();

    $moduleHandler = xoops_getHandler('module');

    $block = [];

    if ($xoopsUser->isAdmin()) {
        if ($moduleHandler->getCount(new Criteria('dirname', 'news'))) {
            $result = $xoopsDB->query('SELECT COUNT(*) FROM ' . $xoopsDB->prefix('stories') . ' WHERE published=0');

            if ($result) {
                [$rows] = $xoopsDB->fetchRow($result);

                if ($rows > 0) {
                    $block['modules'][0]['adminlink'] = XOOPS_URL . '/modules/news/admin/index.php?op=newarticle';

                    $block['modules'][0]['pendingnum'] = $rows;

                    $block['modules'][0]['lang_linkname'] = "<span style='color: #FF0000'>" . _MB_XFMOD_SUBMS . '</span>';
                }
            }
        }

        $result = $xoopsDB->query('SELECT COUNT(*) FROM ' . $xoopsDB->prefix('xoopscomments') . ' WHERE com_status=1');

        if ($result) {
            [$rows] = $xoopsDB->fetchRow($result);

            if ($rows > 0) {
                $block['modules'][7]['adminlink'] = XOOPS_URL . '/modules/system/admin.php?module=0&status=1&fct=comments';

                $block['modules'][7]['pendingnum'] = $rows;

                $block['modules'][7]['lang_linkname'] = "<span style='color: #FF0000'>" . _MB_XFMOD_COMPEND . '</span>';
            }
        }

        $result = $xoopsDB->query('SELECT group_id FROM ' . $xoopsDB->prefix('xf_groups') . " WHERE status='P'");

        if ($result) {
            $rows = $xoopsDB->getRowsNum($result);

            if ($rows > 0) {
                $block['modules'][8]['lang_linkname'] = "<span style='color: #FF0000'>" . _MB_XFMOD_PPROJECTS . '</span>';

                $block['modules'][8]['pendingnum'] = $rows;

                $block['modules'][8]['adminlink'] = XOOPS_URL . '/modules/xfmod/admin.php?fct=groups&op=GroupApprove';
            }
        }

        $date = time() - 60 * 60 * 24 * 14;

        $query = 'SELECT count(id) FROM ' . $xoopsDB->prefix('xf_news_bytes') . ' AS nb' . ', ' . $xoopsDB->prefix('xf_groups') . ' AS g' . " WHERE is_approved=0 AND nb.group_id=g.group_id AND g.is_public=1 AND g.status='A' AND date>$date";

        $result = $xoopsDB->query($query);

        if ($result) {
            [$rows] = $xoopsDB->fetchRow($result);

            if ($rows > 0) {
                $block['modules'][9]['lang_linkname'] = "<span style='color: #FF0000'>" . _MB_XFMOD_PNEWS . '</span>';

                $block['modules'][9]['pendingnum'] = $rows;

                $block['modules'][9]['adminlink'] = XOOPS_URL . '/modules/xfmod/news/admin/?group_id=';
            }
        }
    }

    $query = 'SELECT COUNT(*) FROM ' . $xoopsDB->prefix('priv_msgs') . ' WHERE to_userid = ' . $xoopsUser->getVar('uid') . ' AND read_msg=0';

    $result = $xoopsDB->query($query);

    if ($result) {
        [$rows] = $xoopsDB->fetchRow($result);

        if ($rows > 0) {
            $block['modules'][10]['lang_linkname'] = "<span style='color: #FF0000'>" . _MB_XFMOD_MESSAGES . '</span>';

            $block['modules'][10]['pendingnum'] = $rows;

            $block['modules'][10]['adminlink'] = XOOPS_URL . '/viewpmsg.php';
        }
    }

    return $block;
}

function b_xfmod_howdoi()
{
    //global $_SERVER['PHP_SELF'];

    global $xoopsDB;

    $block['title'] = _MB_XFMOD_HOWDOI;

    $block['content'] = '<table border=0 cellpadding=0 cellspacing=0>';

    $path = dirname($_SERVER['PHP_SELF']);

    $sql = 'SELECT title, help_url FROM ' . $xoopsDB->prefix('xf_context_sensitive_help') . " WHERE for_page='" . $_SERVER['PHP_SELF'] . "' ORDER BY weight";

    $result = $xoopsDB->query($sql);

    if ($result) {
        while (false !== ($row = $xoopsDB->fetchArray($result))) {
            $block['content'] .= "<tr><td class='newsText'><img src='" . XOOPS_URL . "/modules/xfmod/images/n_arrows_grey.gif' width='7' height='7' border='0' alt=''>  <a href='" . $row['help_url'] . "'>" . $row['title'] . '</a></td></tr>';
        }
    }

    $sql = 'SELECT title, help_url FROM ' . $xoopsDB->prefix('xf_context_sensitive_help') . " WHERE for_page='" . $path . "' ORDER BY weight";

    $result = $xoopsDB->query($sql);

    if ($result) {
        while (false !== ($row = $xoopsDB->fetchArray($result))) {
            $block['content'] .= "<tr><td class='newsText'><img src='" . XOOPS_URL . "/modules/xfmod/images/n_arrows_grey.gif' width='7' height='7' border='0' alt=''>  <a href=\"" . $row['help_url'] . '">' . $row['title'] . '</a></td></tr>';
        }
    }

    $block['content'] .= "<tr><td class='newsText'><img src='" . XOOPS_URL . "/modules/xfmod/images/n_arrows_grey.gif' width='7' height='7' border='0' alt=''>  <a href='" . XOOPS_URL . "/modules/xfmod/help/about.php'>Get Help?</a></td></tr></table>";

    return $block;
}
