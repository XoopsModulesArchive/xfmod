<?php

require_once '../../../mainfile.php';
$langfile = 'maillist.php';
require_once XOOPS_ROOT_PATH . '/modules/xfmod/include/pre.php';
require_once XOOPS_ROOT_PATH . '/modules/xfmod/include/vote_function.php';
require_once XOOPS_ROOT_PATH . '/modules/xfmod/include/vars.php';
require_once XOOPS_ROOT_PATH . '/modules/xfmod/news/news_utils.php';
require_once XOOPS_ROOT_PATH . '/modules/xfmod/include/trove.php';
require_once XOOPS_ROOT_PATH . '/modules/xfmod/include/project_summary.php';
require_once XOOPS_ROOT_PATH . '/modules/xfmod/maillist/maillist_utils.php';

$mmsvr = 'https://www.xoops.org/mailman/admin/';

$url = $mmsvr . $prjname . '-' . $mlname;

if ($id) {
    $project = &group_get_object($id);

    $perm = &$project->getPermission($xoopsUser);

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

    //OpenTable();

    echo "\n<b>" . _LOCAL_XF_G_PROJECT . ': ' . $project->getPublicName() . "</b><br><br>\n";

    echo "<b style='font-size:16px;align:left;'>" . _LOCAL_XF_ML_FULLNAME . "</b><br>\n";

    project_tabs('maillist', $id);

    echo "<p>\n";

    include $url;

    //CloseTable();

    include '../../../footer.php';
} else {
    redirect_header($GLOBALS['HTTP_REFERER'], 4, 'Error<br>No Group');

    exit;
}
