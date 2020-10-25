<?php

require_once '../../../mainfile.php';

getRandomProject();

function getRandomProject()
{
    global $xoopsDB;

    $limit = 500;

    $sql = 'SELECT unix_group_name FROM ' . $xoopsDB->prefix('xf_groups') . " WHERE status='A'" . ' AND is_public=1' . ' ORDER BY register_time DESC';

    $result = $xoopsDB->query($sql, $limit);

    $rows = $xoopsDB->getRowsNum($result);

    $rand = mt_rand(1, $rows);

    for ($count = 1; $count <= $rand; $count++) {
        $row = $xoopsDB->fetchArray($result);

        if ($count == $rand) {
            redirect_header(XOOPS_URL . '/modules/xfmod/project/?' . $row['unix_group_name'], 0);

            exit();
        }
    }
}
