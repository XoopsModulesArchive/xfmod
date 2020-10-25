<?php

require_once '../../mainfile.php';
require_once XOOPS_ROOT_PATH . '/modules/xfmod/include/pre.php';

$res_package = $xoopsDB->query(
    'SELECT r.release_date,r.name,r.release_id ' . 'FROM ' . $xoopsDB->prefix('xf_frs_package') . ' p, ' . $xoopsDB->prefix('xf_frs_release') . ' r ' . "WHERE p.group_id='109' " . "AND p.package_id='9' " . 'AND p.package_id=r.package_id ' . 'ORDER BY r.release_date DESC'
);

if ($xoopsDB->getRowsNum($res_package) > 0) {
    $arr = $xoopsDB->fetchArray($res_package);

    $ret = $arr['name'] . '|' . date('m/d/Y', $arr['release_date']);

    $res_release = $xoopsDB->query(
        'SELECT f.filename,f.file_url ' . 'FROM ' . $xoopsDB->prefix('xf_frs_file') . ' f, ' . $xoopsDB->prefix('xf_frs_release') . ' r ' . 'WHERE f.release_id=r.release_id ' . "AND r.release_id='" . $arr['release_id'] . "'"
    );

    while (false !== ($row = $xoopsDB->fetchArray($res_release))) {
        $ret .= '|' . $row['filename'] . ',' . $row['file_url'];
    }

    echo $ret;
} else {
    echo 'none';
}
