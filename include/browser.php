<?php
//
// SourceForge: Breaking Down the Barriers to Open Source Development
// Copyright 1999-2000 (c) The SourceForge Crew
// http://sourceforge.net
//
// $Id: browser.php,v 1.2 2003/12/09 15:03:53 devsupaul Exp $

unset($BROWSER_AGENT);
unset($BROWSER_VER);
unset($BROWSER_PLATFORM);

function browser_get_agent()
{
    global $BROWSER_AGENT;

    return $BROWSER_AGENT;
}

function browser_get_version()
{
    global $BROWSER_VER;

    return $BROWSER_VER;
}

function browser_get_platform()
{
    global $BROWSER_PLATFORM;

    return $BROWSER_PLATFORM;
}

function browser_is_mac()
{
    if ('Mac' == browser_get_platform()) {
        return true;
    }
  

    return false;
}

function browser_is_windows()
{
    if ('Win' == browser_get_platform()) {
        return true;
    }
  

    return false;
}

function browser_is_ie()
{
    if ('IE' == browser_get_agent()) {
        return true;
    }
  

    return false;
}

function browser_is_netscape()
{
    if ('MOZILLA' == browser_get_agent()) {
        return true;
    }
  

    return false;
}

/*
    Determine browser and version
*/

if (preg_match('MSIE ([0-9].[0-9]{1,2})', $_SERVER['HTTP_USER_AGENT'], $log_version)) {
    $BROWSER_VER = $log_version[1];

    $BROWSER_AGENT = 'IE';
} elseif (preg_match('Opera ([0-9].[0-9]{1,2})', $_SERVER['HTTP_USER_AGENT'], $log_version)) {
    $BROWSER_VER = $log_version[1];

    $BROWSER_AGENT = 'OPERA';
} elseif (preg_match('Mozilla/([0-9].[0-9]{1,2})', $_SERVER['HTTP_USER_AGENT'], $log_version)) {
    $BROWSER_VER = $log_version[1];

    $BROWSER_AGENT = 'MOZILLA';
} else {
    $BROWSER_VER = 0;

    $BROWSER_AGENT = 'OTHER';
}

/*
    Determine platform
*/

if (mb_strstr($_SERVER['HTTP_USER_AGENT'], 'Win')) {
    $BROWSER_PLATFORM = 'Win';
} elseif (mb_strstr($_SERVER['HTTP_USER_AGENT'], 'Mac')) {
    $BROWSER_PLATFORM = 'Mac';
} elseif (mb_strstr($_SERVER['HTTP_USER_AGENT'], 'Linux')) {
    $BROWSER_PLATFORM = 'Linux';
} elseif (mb_strstr($_SERVER['HTTP_USER_AGENT'], 'Unix')) {
    $BROWSER_PLATFORM = 'Unix';
} else {
    $BROWSER_PLATFORM = 'Other';
}

/*
echo "\n\nAgent: $HTTP_USER_AGENT";
echo "\nIE: ".browser_is_ie();
echo "\nMac: ".browser_is_mac();
echo "\nWindows: ".browser_is_windows();
echo "\nPlatform: ".browser_get_platform();
echo "\nVersion: ".browser_get_version();
echo "\nAgent: ".browser_get_agent();
*/
