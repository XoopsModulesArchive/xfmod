<?php

require_once '../../../mainfile.php';
$langfile = 'project.php';

require_once XOOPS_ROOT_PATH . '/modules/xfmod/include/pre.php';
require_once XOOPS_ROOT_PATH . '/modules/xfmod/include/httpClient.php';
require_once XOOPS_ROOT_PATH . '/modules/xfmod/include/checkurl.php';

global $xoopsForge;

$pathinfo = checkURL($_SERVER['PATH_INFO']);

if (file_exists('dnsmap.php')) {
    require_once 'dnsmap.php';
}

// If get parameters are in the URL
// then ensure they are put back on
$isparms = false;

if (count($_GET)) {
    $i = 0;

    foreach ($_GET as $argname => $argval) {
        $isparms = true;

        if ($i > 0) {
            $pathinfo .= '&' . $argname . '=' . $argval;
        } else {
            $pathinfo .= '/?' . $argname . '=' . $argval;
        }

        $i++;
    }
}

if (!$isparms) {
    $pos = mb_strrpos($pathinfo, '/');

    if (false === $pos || ($pos + 1) < mb_strlen($pathinfo)) {
        $pathinfo .= '/';
    }
}

// Parse URL to get project name
$projname = trim($_SERVER['PATH_INFO'], '/');

// do not impose access control on image requests (also in path info)
$image_request = preg_match('/.*\/images\/.+\.png/i', $_SERVER['PATH_INFO']);

// $_SERVER['PATH_INFO'] will be null for .../#dirlist
$inproject = (!empty($projname) && !$image_request);

// add permission checking for preventing access to private projects
//   (code taken from xfmod/project/index.php)
if ($inproject) {
    $projcomps = explode('/', $projname);

    $projname = $projcomps[0];

    if (false !== strpos($projname, '*')) {
        $projname = $projcomps[1];
    }

    // find group id based on the project name

    $res = $xoopsDB->query('SELECT group_id FROM ' . $xoopsDB->prefix('xf_groups') . " WHERE unix_group_name='" . strtok($projname, '&') . "'");

    $group_arr = $xoopsDB->fetchArray($res);

    $group_id = $group_arr['group_id'];

    // create a project object

    $project = &group_get_object($group_id);

    // project does not exist

    if (!$project) {
        redirect_header(XOOPS_URL, 2, _XF_PRJ_PROJECTDOESNOTEXIST);

        exit;
    }

    // retrieve project permissions for the current user

    $perm = &$project->getPermission($xoopsUser);

    // project is private

    if (!$project->isPublic()) {
        // if it's a private project, you must be a member of that project

        if (!$project->isMemberOfGroup($xoopsUser) && !$perm->isSuperUser()) {
            redirect_header(XOOPS_URL . '/', 4, _XF_PRJ_PROJECTMARKEDASPRIVATE);

            exit;
        }
    }

    // for projects that do not allow anonymous access, you have to be a project member

    if (!$project->anonCVS()) {
        if (!$project->isMemberOfGroup($xoopsUser) && !$perm->isSuperUser()) {
            redirect_header(XOOPS_URL, 4, _XF_PRJ_NOTAUTHORIZEDTOENTER);

            exit;
        }
    }

    // first, for inactive projects, you have to be a project admin or superuser

    if ($project->isInactive() && !$perm->isSuperUser() && !$perm->isAdmin()) {
        redirect_header(XOOPS_URL, 4, _XF_PRJ_NOTAUTHORIZEDTOENTER);

        exit;
    }

    // for dead projects must be member of xoopsforge project

    if (!$project->isActive() && !$project->isInactive() && !$perm->isSuperUser()) {
        redirect_header(XOOPS_URL, 4, _XF_PRJ_NOTAUTHORIZEDTOENTER);

        exit;
    }
} else {
    // do not impose access control on image requests

    if (!$image_request) {
        // forbid access beyond individual projects - TODO feature for webmasters

        redirect_header(XOOPS_URL, 4, _XF_PRJ_NOTAUTHORIZEDTOENTER);

        exit;
    }
}

$cvsdns = $xoopsForge['cvs_url'];

$cvshost = 'http://' . $cvsdns . ':80';
$cvsscript = '/' . $xoopsForge['cvs_script'];
$cvssvr = $cvshost . $cvsscript;

require_once XOOPS_ROOT_PATH . '/modules/xfmod/include/pre.php';
require_once XOOPS_ROOT_PATH . '/modules/xfmod/include/vote_function.php';
require_once XOOPS_ROOT_PATH . '/modules/xfmod/include/vars.php';
require_once XOOPS_ROOT_PATH . '/modules/xfmod/news/news_utils.php';
require_once XOOPS_ROOT_PATH . '/modules/xfmod/include/trove.php';
require_once XOOPS_ROOT_PATH . '/modules/xfmod/include/project_summary.php';

$http = new Net_HTTP_Client();
if (!$http->Connect($cvsdns, 80)) {
    echo "<b>ViewCVS is not available on server $cvsdns</b><br>";

    exit();
}

$relurl = '/' . $xoopsForge['cvs_script'] . $pathinfo;
$status = $http->Get($relurl);
if (200 != $status) {
    echo '<b>Problem contacting server ' . $cvsdns . ' : ' . $http->getStatusMessage() . '</b><br>';

    $http->Disconnect();

    exit();
}  
    $buf = $http->getBody();
    $cheaders = $http->getHeaders();
    $contype = $cheaders['Content-Type'];

    if (0 !== strncasecmp($contype, 'text/html', 9)) {
        header('Content-type: ' . $contype);

        echo $buf;
    } else {
        //schalmn:

        /*
        $res = $xoopsDB->query("SELECT group_id FROM ".$xoopsDB->prefix("xf_groups")." WHERE unix_group_name='".$projname."'");
        if(!$res || $xoopsDB->getRowsNum($res) < 1)
        {
                $xoopsForgeErrorHandler->setSystemError("Invalid project id passed to the cvs page");
        }
        else
        {
                  $group_arr = $xoopsDB->fetchArray($res);
                  $group_id = $group_arr['group_id'];
        }

        $project =& group_get_object($group_id);
        */

        //end

        include '../../../header.php';

        //project nav information

        echo project_title($project);

        echo project_tabs('CVS', $group_id);

        $patterns = ["/<a.*\[Development\]<\/a>/"];

        $replaces = [''];

        $buf = preg_replace($patterns, $replaces, $buf);

        $patterns2 = ["/<a.*\[cvs]\<\/a>/"];

        $replaces2 = [''];

        $buf = preg_replace($patterns2, $replaces2, $buf);

        $patterns3 = ['/h1/'];

        $replaces3 = ['h3'];

        $buf = preg_replace($patterns3, $replaces3, $buf);

        $patterns4 = ["/<a.*\Back to SourceForge.net\<\/a>/"];

        $replaces4 = [''];

        $buf = preg_replace($patterns4, $replaces4, $buf);

        $replace = $_SERVER['SCRIPT_NAME'];

        $buf = str_replace($cvssvr, $replace, $buf);

        $buf = str_replace($cvsscript, $replace, $buf);

        // Other directories than XOOPS_URL may not reachable when going via a reverse proxy.

        // Solution: put ViewCVS under XOOPS_URL and specify its URL as a config parameter.

        $buf = str_replace('/icons/', $xoopsForge['cvs_view_url'] . '/icons/', $buf);

        echo $buf;

        include '../../../footer.php';
    }

$http->Disconnect();
