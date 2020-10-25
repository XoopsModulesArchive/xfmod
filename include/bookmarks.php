<?php
/**
 * Bookmarks functions library.
 *
 * SourceForge: Breaking Down the Barriers to Open Source Development
 * Copyright 1999-2001 (c) VA Linux Systems
 * http://sourceforge.net
 *
 * @version   $Id: bookmarks.php,v 1.3 2003/12/09 15:03:53 devsupaul Exp $
 * @param mixed $bookmark_url
 * @param mixed $bookmark_title
 */

/**
 * bookmark_add() - Add a new bookmark
 *
 * @param string $bookmark_url   The bookmark's URL
 * @param string $bookmark_title The bookmark's title
 * @return bool
 * @return bool
 */
function bookmark_add($bookmark_url, $bookmark_title = '')
{
    global $xoopsDB, $xoopsUser;

    if (!$bookmark_title) {
        $bookmark_title = $bookmark_url;
    }

    $result = $xoopsDB->queryF(
        'INSERT INTO ' . $xoopsDB->prefix('xf_user_bookmarks') . ' (' . 'user_id, bookmark_url,bookmark_title) values (' . "'" . $xoopsUser->getVar('uid') . "'," . "'$bookmark_url'," . "'$bookmark_title')"
    );

    if (!$result) {
        return false;
    }

    return true;
}

/**
 * bookmark_edit() - Edit an existing bookmark
 *
 * @param mixed $bookmark_id
 * @param mixed $bookmark_url
 * @param mixed $bookmark_title
 */
function bookmark_edit($bookmark_id, $bookmark_url, $bookmark_title)
{
    global $xoopsDB, $xoopsUser;

    $xoopsDB->queryF(
        'UPDATE ' . $xoopsDB->prefix('xf_user_bookmarks') . ' SET ' . "bookmark_url='$bookmark_url'," . "bookmark_title='$bookmark_title' " . "WHERE bookmark_id='$bookmark_id' " . "AND user_id='" . $xoopsUser->getVar('uid') . "'"
    );
}

/**
 * bookmark_deleted() - Delete an existing bookmark
 *
 * @param mixed $bookmark_id
 */
function bookmark_delete($bookmark_id)
{
    global $xoopsDB, $xoopsUser;

    $xoopsDB->queryF(
        'DELETE FROM ' . $xoopsDB->prefix('xf_user_bookmarks') . ' ' . "WHERE bookmark_id='$bookmark_id' " . "AND user_id='" . $xoopsUser->getVar('uid') . "'"
    );
}
