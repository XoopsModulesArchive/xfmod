<?php
/**
*
* SourceForge Survey Facility
*
* SourceForge: Breaking Down the Barriers to Open Source Development
* Copyright 1999-2001 (c) VA Linux Systems
* http://sourceforge.net
*
* @version $Id: show_results_comments.php,v 1.1 2004/04/25 09:22:34 praedator Exp $
*
*/
include_once ("../../../../mainfile.php");

$langfile="survey.php";
require_once(XOOPS_ROOT_PATH."/modules/xfmod/include/pre.php");
require_once(XOOPS_ROOT_PATH."/modules/xfmod/include/project_summary.php");
require_once(XOOPS_ROOT_PATH."/modules/xfmod/survey/survey_utils.php");
require_once(XOOPS_ROOT_PATH."/modules/xfmod/include/HTML_Graphs.php");
$xoopsOption['template_main'] = 'survey/admin/xfmod_show_results_comments.html';

project_check_access ($group_id);

// get current information
$group =& group_get_object($group_id);
$perm =& $group->getPermission( $xoopsUser );

if(!$perm->isAdmin())
{
$xoopsForgeErrorHandler->setSystemError(_XF_SUR_NOTALLOWED);
}

include (XOOPS_ROOT_PATH."/header.php");
$header = survey_header($group, _XF_SUR_SURVEYAGGREGATERESULTS, 'is_admin_page');
$xoopsTpl->assign("survey_header",$header);

function ShowResultComments($result) {
global $survey_id, $xoopsConfig, $xoopsDB;
$rows = $xoopsDB->getRowsNum($result);
$cols = unofficial_getNumFields($result);

echo "<h4>".sprintf(_XF_SUR_XFOUND, $rows)."</h4>";

echo "<table border='0' width='100%'><tr class='bg2'>";
for ($i = 0; $i < $cols; $i++) {
printf("<TD><b>%s</b></td>\n", unofficial_getFieldName($result, $i));
}
echo "</tr>";

for($j = 0; $j < $rows; $j++) {

echo "<TR class='".($j%2>0?'bg1':'bg3')."'>\n";
for ($i = 0; $i < $cols; $i++) {
printf("<TD>%s</TD>\n",unofficial_getDBResult($result,$j,$i));
}

echo "</tr>";
}
echo "</table>";
}

$sql = "SELECT question FROM ".$xoopsDB->prefix("xf_survey_questions")." WHERE question_id='$question_id'";
$result = $xoopsDB->query($sql);
echo "<h4>"._XF_SUR_QUESTION.": ".$ts->makeTboxData4Show(unofficial_getDBResult($result,0,"question"))."</H4>";
echo "<P>";

$sql = "SELECT DISTINCT response FROM ".$xoopsDB->prefix("xf_survey_responses")." WHERE survey_id='$survey_id' AND question_id='$question_id' AND group_id='$group_id'";
$result = $xoopsDB->query($sql);
ShowResultComments($result);

include (XOOPS_ROOT_PATH."/footer.php");

?>