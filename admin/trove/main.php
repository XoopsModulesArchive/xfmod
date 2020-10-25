<?php

if (!eregi('admin.php', $_SERVER['PHP_SELF'])) {
    die('Access Denied');
}

require_once XOOPS_ROOT_PATH . '/modules/xfmod/include/pre.php';
require_once XOOPS_ROOT_PATH . '/modules/xfmod/include/trove.php';
require_once 'admin/admin_utils.php';
require_once 'admin/trove/trove.php';

$op = util_http_track_vars('op');

// trove parametes
$shortname = util_http_track_vars('shortname');
$fullname = util_http_track_vars('fullname');
$description = util_http_track_vars('description');
$parent = util_http_track_vars('parent');
$trove_cat_id = util_http_track_vars('trove_cat_id');

switch ($op) {
    case 'TroveAdd':
        TroveAdd();
        break;
    case 'TroveInsert':

        TroveInsert($shortname, $fullname, $description, $parent);
        break;
    case 'TroveEdit':
        TroveEdit($trove_cat_id);
        break;
    case 'TroveSave':
        TroveSave($trove_cat_id, $shortname, $fullname, $description, $parent);
        break;
    case 'TroveList':
        TroveList();
        break;
    default:
        TroveList();
        break;
}
