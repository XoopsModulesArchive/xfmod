<?php

function themesidebox($title, $content)
{
    echo "<table width=\"95%\" border=\"0\" cellspacing=\"0\" cellpadding=\"2\">

    <tr>
    <TH><b>$title</b></TH>

    


    </tr>

    <tr>
    
    <td valign=\"top\">$content</td>
    </tr>

    </table><br>";
}

function themecenterposts($title, $content)
{
    echo "<br><table width=\"95%\" border=\"0\" cellspacing=\"1\" cellpadding=\"5\" cellpadding=\"0\" bgcolor=\"#E2DBD3\"> <tr><td bgcolor=\"#FFAA00\">&nbsp;<b><font color=\"#000000\">$title</font></b></td></tr><tr><td bgcolor=\"#EFEFEF\">$content</td></tr></table>";
}
