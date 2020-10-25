<?php
//$cvspath = $_GET['path'];

require_once '../../../mainfile.php';
$langfile = 'maillist.php';
require_once XOOPS_ROOT_PATH . '/modules/xfmod/include/pre.php';
require_once XOOPS_ROOT_PATH . '/modules/xfmod/include/vote_function.php';
require_once XOOPS_ROOT_PATH . '/modules/xfmod/include/vars.php';
require_once XOOPS_ROOT_PATH . '/modules/xfmod/news/news_utils.php';
require_once XOOPS_ROOT_PATH . '/modules/xfmod/include/trove.php';
require_once XOOPS_ROOT_PATH . '/modules/xfmod/include/project_summary.php';
require_once XOOPS_ROOT_PATH . '/modules/xfmod/maillist/maillist_utils.php';
require_once XOOPS_ROOT_PATH . '/modules/xfmod/include/httpClient.php';
require_once XOOPS_ROOT_PATH . '/modules/xfmod/include/checkurl.php';

$cvspath = util_http_track_vars('path');
$pathinfo = checkURL($_SERVER['PATH_INFO']);
$mmsvr = '';
if (file_exists('mlserver.php')) {
    require_once 'mlserver.php';

    $mmsvr = getSvrName();
} else {
    $mmsvr = $_SERVER['HTTP_HOST'];
}
$archive = maillist_get_archive_path();
$querystring = $_SERVER['QUERY_STRING'];
if ('/' == $archive[mb_strlen($archive) - 1] && '/' == $pathinfo[0]) {
    $archive = mb_substr($archive, 0, -1);
}
$url = $archive . $pathinfo;
if ('/' != $url[mb_strlen($url) - 1] && !mb_strstr($url, 'mbox')) {
    $lastslash = mb_strrpos($url, '/');

    $laststr = mb_substr($url, $lastslash + 1, mb_strlen($url) - $lastslash);

    if (mb_strstr($laststr, $prjname)) {
        $url .= '/';
    }
}
if (mb_strlen($querystring)) {
    $url .= '?' . $querystring;
}

if ($id) {
    $project = &group_get_object($id);

    $perm = &$project->getPermission($xoopsUser);

    //group is private

    if (!$project->isPublic()) {
        //if it's a private group, you must be a member of that group

        if (!$project->isMemberOfGroup($xoopsUser) && !$perm->isSuperUser()) {
            redirect_header(XOOPS_URL . '/', 4, _XF_PRJ_PROJECTMARKEDASPRIVATE);

            exit;
        }
    }

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

    echo project_title($project);

    //echo "<b style='font-size:16px;align:left;'>" . _LOCAL_XF_ML_FULLNAME . "</b><br>\n";

    echo project_tabs('maillist', $id);

    echo "<p>\n";

    $fp = fsockopen($mmsvr, 80);

    if ($fp) {
        fwrite($fp, "GET $url HTTP/1.0\r\n");

        fwrite($fp, "Host: $mmsvr\r\n\r\n");

        while (!feof($fp)) {
            $page .= fread($fp, 512);
        }

        fclose($fp);

        $pieces = explode("\r\n\r\n", $page, 2);

        $buf = $pieces[1];
    } else {
        echo "could not open $mmsvr";
    }

    /*
        $http = new Net_HTTP_Client();
        if ( ! $http->Connect( $mmsvr ) )
        {
        echo "GNU Mailman is not available.";
        }
        $status = $http->Get($url);
        if ( $status != 200 )
        {
            if ( $status == 404 )
            {
                echo "Archive is empty.<br>\n";
            }
            else
            {
                echo "GNU Mailman is not available.  (error is ".$http->getStatusMessage().")";
            }
            include "../../../footer.php";
            $conn->Disconnect();
            exit();
        }
        $buf = $http->getBody();
        $hdrs = $http->getHeaders();
    */

    $do_crfix = $_GET['docrfix'];

    // Do this if the object we retrieved is a text file.

    if ('' != $do_crfix) {
        $buf = str_replace("\n", '<br>', $buf);
    }

    // Tell any hrefs for .txt files to do crfix

    $buf = str_replace('.txt', ".txt?docrfix=yes&$querystring", $buf);

    $buf = str_replace('.html', ".html?$querystring", $buf);

    echo $buf;

    include '../../../footer.php';
} else {
    redirect_header($GLOBALS['HTTP_REFERER'], 4, 'Error<br>No Group');

    exit;
}
