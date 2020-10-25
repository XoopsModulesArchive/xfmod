<?php

global $type;

if ('community' == $type) {
    $project = ' or project';
}

define(
    '_XF_STEP5',
    '<p>

In addition to full ' . $type . ' name, you will also need to choose a "Short Name" name for your ' . $type . '.

</p>



<P> The "Short Name" has several restrictions because it is

used in so many places around the Forge site. Your ' . $type . ' short name:



<UL>

<LI>Cannot match the short name of any other ' . $type . ' ' . $project . '

<LI>Must be between 3 and 15 characters in length

<LI>Must be in lower case

<LI>Can only contain characters, numbers, and dashes

<LI>Must start with a letter

<LI>Cannot match any reserved XOOPS Developent Forge name

<LI>Will never change for this ' . $type . '

</UL>'
);
