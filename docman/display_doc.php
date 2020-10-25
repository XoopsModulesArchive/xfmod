<?php
/**
 * SourceForge Documentaion Manager
 *
 * SourceForge: Breaking Down the Barriers to Open Source Development
 * Copyright 1999-2001 (c) VA Linux Systems
 * http://sourceforge.net
 *
 * @version   $Id: display_doc.php,v 1.7 2004/12/15 10:51:03 mercibe Exp $
 */
require_once '../../../mainfile.php';

$langfile = 'docman.php';
require_once XOOPS_ROOT_PATH . '/modules/xfmod/include/pre.php';
require_once XOOPS_ROOT_PATH . '/modules/xfmod/docman/doc_utils.php';
require_once XOOPS_ROOT_PATH . '/modules/xfmod/include/mime_lookup.php';
$GLOBALS['xoopsOption']['template_main'] = 'docman/xfmod_docs_show.html';

$content = '';
if ($docid) {
    if ($answer) {
        if (!$suggestion) {
            $suggestion = '';
        }

        if ($xoopsUser) {
            $user_id = $xoopsUser->getVar('uid');
        } else {
            $user_id = 100;
        }

        $res = $xoopsDB->queryF(
            'INSERT INTO ' . $xoopsDB->prefix('xf_doc_feedback') . ' ' . '(docid,user_id,answer,suggestion,entered) VALUES ' . "('$docid','$user_id','$answer','" . $ts->addSlashes($suggestion) . "'," . time() . ')'
        );

        redirect_header($GLOBALS['HTTP_REFERER'], 2, _XF_DOC_THANKYOUFORFEEDBACK);

        exit;
    }

    //first take the doc

    $query1 = 'SELECT * ' . 'FROM ' . $xoopsDB->prefix('xf_doc_data') . " WHERE docid='$docid'";

    $result1 = $xoopsDB->query($query1);

    //check if it exsits

    if ($xoopsDB->getRowsNum($result1) < 1) {
        redirect_header($GLOBALS['HTTP_REFERER'], 4, _XF_DOC_DOCUMENTUNAVAILABLE);

        exit;
    }  

    //check status (1-> ok, 5 -> requesting user has to be in the project)

    $row = $xoopsDB->fetchArray($result1);

    switch ($row['stateid']) {
            case 1:
                break;
            case 5:
                $query2 = 'SELECT * ' . 'FROM ' . $xoopsDB->prefix('xf_user_group') . " WHERE user_id='" . $xoopsUser->getVar('uid') . "' " . " AND group_id = '$group_id'";

                $result2 = $xoopsDB->query($query2);
                if ($xoopsDB->getRowsNum($result2) < 1) {
                    redirect_header($GLOBALS['HTTP_REFERER'], 4, _XF_DOC_PRIVATEDOCUMENTUNAVAILABLE);

                    exit;
                }
                break;
            default:
                redirect_header($GLOBALS['HTTP_REFERER'], 4, _XF_DOC_DOCUMENTUNAVAILABLE);
                exit;
        }

    $project = &group_get_object($group_id);

    require XOOPS_ROOT_PATH . '/header.php';

    $xoopsTpl->assign('docman_header', docman_header($project, $group_id, _XF_DOC_PROJECTDOCUMENTATION));

    // data in DB stored in htmlspecialchars()-encoded form

    // added urldecode() to filename

    $content .= $ts->displayTarea($row['data'], 1, 1, 1);

    if (false === mb_strpos(rawurldecode($row['data']), '/')) {
        $content = '<a href="' . XOOPS_URL . $xoopsForge['dl_url'] . '/' . $project->getUnixName() . '/docs/' . $content . '">' . rawurldecode($content) . '</a>';
    } else {
        $content = '<a href="' . rawurldecode($row['data']) . '" target="_blank">' . $row['title'] . '</a>';
    }

    $xoopsTpl->assign('docs_title', _XF_DOC_FEEDBACK);

    $contents = '';

    $contents .= "<form action='"
                 . $_SERVER['PHP_SELF']
                 . "' method='post'>"
                 . _XF_DOC_FEEDBACKWILLHELPUS
                 . '<p>'
                 . _XF_DOC_DIDARTICLEANSWERYOURQUESTION
                 . '<br>'
                 . "<input type='hidden' name='docid' value='"
                 . $docid
                 . "'>"
                 . "<input type='Radio' name='answer' value='2' checked> "
                 . _YES
                 . ' <br>'
                 . "<input type='Radio' name='answer' value='1'> "
                 . _NO
                 . ' <br>'
                 . "<input type='Radio' name='answer' value='0'> "
                 . _XF_DOC_DIDNOTAPPLY
                 . ' <p>'
                 . _XF_DOC_SUGGESTION
                 . ':<br>'
                 . "<textarea name='suggestion' cols='15' rows='5' ></textarea>"
                 . '<p>'
                 . "<input type='submit' name='submit' value='"
                 . _XF_G_SUBMIT
                 . "'>"
                 . '</form>';

    $xoopsTpl->assign('docs_content', $contents);

    $xoopsTpl->assign('content', $content);

    require XOOPS_ROOT_PATH . '/footer.php';
} else {
    redirect_header($GLOBALS['HTTP_REFERER'], 4, '' . _XF_DOC_NODOCUMENTTODISPLAY);

    exit;
}
