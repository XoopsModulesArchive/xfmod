<?php

require_once 'config.inc';
// register parameters
$mygroup = $_REQUEST['group'];
$first = $_REQUEST['first'];
$last = $_REQUEST['last'];

$title .= ' - ' . $mygroup;
require_once 'head.inc';

?>

<a name="top">
<h1 align="center"><?php echo $mygroup; ?></h1>

<p align="center">
    <?php
    if (!$readonly) {
        if (null != $xoopsUser) {
            echo '[<a href="' . $file_post . '?group_id=' . $group_id . '&newsgroups=' . urlencode($mygroup) . '&amp;type=new">' . $text_thread['button_write'] . '</a>] ';
        } else {
            echo '[<a href="' . XOOPS_URL . '/user.php?xoops_redirect=' . $_SERVER['PHP_SELF'] . '?' . urlencode($_SERVER['QUERY_STRING']) . '">Log in to post</a>]';
        }
    }
    //  echo '[<a href="'.$file_groups.'">'.$text_thread["button_grouplist"].'</a>]';
    ?>
</p>

<?php
require_once (string)$file_newsportal;
$ns = OpenNNTPconnection($server, $port);
flush();
if (false !== $ns) {
    if ($first > $maxarticles || $last > $maxarticles) {
        $old_articles = true;
    } else {
        $old_articles = false;
    }

    $total_count = $article_count = getNumArticles($ns, $mygroup);

    if ($old_articles) {
        $headers = readOverview($ns, $mygroup, 1, false, $first, $last);
    } else {
        $headers = readOverview($ns, $mygroup, 1);

        $article_count = count($headers);
    }

    if (0 != $articles_per_page) {
        if ((!isset($first)) || (!isset($last))) {
            if ('first' == $startpage) {
                $first = 1;

                $last = $articles_per_page;
            } else {
                $first = $article_count - (($article_count - 1) % $articles_per_page);

                $last = $article_count;
            }
        }

        $page_menu = getPageSelectMenu($mygroup, $total_count, $first);

        echo '<p align="center">' . $page_menu . '</p>';
    } else {
        $first = 0;

        $last = $article_count;
    }

    if ($old_articles) {
        showHeaders($headers, $mygroup, 30, 80);
    } else {
        showHeaders($headers, $mygroup, $first, $last);
    }

    if (0 != $articles_per_page) {
        echo '<p align="center">' . $page_menu . '</p>';
    }

    closeNNTPconnection($ns);
}
?>

<p align="right"><a href="#top"><?php echo $text_thread['button_top']; ?></a></p>

<?php require_once 'tail.inc'; ?>
