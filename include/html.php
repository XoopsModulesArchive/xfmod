<?php
/**
 * Misc HTML functions
 *
 * SourceForge: Breaking Down the Barriers to Open Source Development
 * Copyright 1999-2001 (c) VA Linux Systems
 * http://sourceforge.net
 *
 * @version   $Id: html.php,v 1.4 2004/01/08 20:15:15 devsupaul Exp $
 * @param mixed $vals
 * @param mixed $select_name
 * @param mixed $checked_val
 * @param mixed $samevals
 */

/**
 * html_build_select_box_from_array() - Takes one array, with the first array being the "id"
 * or value and the array being the text you want displayed.
 *
 * @param string $vals        The name you want assigned to this form element
 * @param string $select_name The value of the item that should be checked
 * @param string $checked_val
 * @param int    $samevals
 * @return string
 */
function html_build_select_box_from_array($vals, $select_name, $checked_val = 'xzxz', $samevals = 0)
{
    $return .= '
		<SELECT NAME="' . $select_name . '">';

    $rows = count($vals);

    for ($i = 0; $i < $rows; $i++) {
        if ($samevals) {
            $return .= "\n\t\t<OPTION VALUE=\"" . $vals[$i] . '"';

            if ($vals[$i] == $checked_val) {
                $return .= ' SELECTED';
            }
        } else {
            $return .= "\n\t\t<OPTION VALUE=\"" . $i . '"';

            if ($i == $checked_val) {
                $return .= ' SELECTED';
            }
        }

        $return .= '>' . $vals[$i] . '</OPTION>';
    }

    $return .= '
		</SELECT>';

    return $return;
}

/**
 * html_build_select_box_from_arrays() - Takes two arrays, with the first array being the "id" or value and the other
 * array being the text you want displayed.
 *
 * The infamous '100 row' has to do with the SQL Table joins done throughout all this code.
 * There must be a related row in users, categories, et    , and by default that
 * row is 100, so almost every pop-up box has 100 as the default
 * Most tables in the database should therefore have a row with an id of 100 in it so that joins are successful
 *
 * @param mixed $vals
 * @param mixed $texts
 * @param mixed $select_name
 * @param mixed $checked_val
 * @param mixed $show_100
 * @param mixed $text_100
 * @return string
 */
function html_build_select_box_from_arrays($vals, $texts, $select_name, $checked_val = 'xzxz', $show_100 = true, $text_100 = _XF_G_NONE)
{
    $return = '';

    $return .= '
		<SELECT NAME="' . $select_name . '">';

    //we don't always want the default 100 row shown

    if ($show_100) {
        $return .= '
		<OPTION VALUE="100">' . $text_100 . '</OPTION>';
    }

    $rows = count($vals);

    if (count($texts) != $rows) {
        $return .= 'ERROR - uneven row counts';
    }

    $checked_found = false;

    for ($i = 0; $i < $rows; $i++) {
        //  uggh - sorry - don't show the 100 row

        //  if it was shown above, otherwise do show it

        if (('100' != $vals[$i]) || ('100' == $vals[$i] && !$show_100)) {
            $return .= '
				<OPTION VALUE="' . $vals[$i] . '"';

            if ($vals[$i] == $checked_val) {
                $checked_found = true;

                $return .= ' SELECTED';
            }

            $return .= '>' . $texts[$i] . '</OPTION>';
        }
    }

    //

    //	If the passed in "checked value" was never "SELECTED"

    //	we want to preserve that value UNLESS that value was 'xzxz', the default value

    //

    if (!$checked_found && 'xzxz' != $checked_val && $checked_val && 100 != $checked_val) {
        $return .= '
		<OPTION VALUE="' . $checked_val . '" SELECTED>' . _XF_G_NOCHANGE . '</OPTION>';
    }

    $return .= '
		</SELECT>';

    return $return;
}

/**
 * html_build_select_box() - Takes a result set, with the first column being the "id" or value and
 * the second column being the text you want displayed.
 *
 * @param mixed $result
 * @param mixed $name
 * @param mixed $checked_val
 * @param mixed $show_100
 * @param mixed $text_100
 * @return string
 */
function html_build_select_box($result, $name, $checked_val = 'xzxz', $show_100 = true, $text_100 = _XF_G_NONE)
{
    return html_build_select_box_from_arrays(util_result_column_to_array($result, 0), util_result_column_to_array($result, 1), $name, $checked_val, $show_100, $text_100);
}

/**
 * html_build_multiple_select_box() - Takes a result set, with the first column being the "id" or value
 * and the second column being the text you want displayed.
 *
 * @param mixed $result
 * @param mixed $name
 * @param mixed $checked_array
 * @param mixed $size
 * @param mixed $show_100
 * @return string
 */
function html_build_multiple_select_box($result, $name, $checked_array, $size = '8', $show_100 = true)
{
    global $xoopsDB;

    $checked_count = count($checked_array);

    $return = '
		<SELECT NAME="' . $name . '" MULTIPLE SIZE="' . $size . '">';

    if ($show_100) {
        /*
            Put in the default NONE box
        */

        $return .= '
		<OPTION VALUE="100"';

        for ($j = 0; $j < $checked_count; $j++) {
            if ('100' == $checked_array[$j]) {
                $return .= ' SELECTED';
            }
        }

        $return .= '>' . _XF_G_NONE . '</OPTION>';
    }

    $rows = $xoopsDB->getRowsNum($result);

    for ($i = 0; $i < $rows; $i++) {
        if (('100' != unofficial_getDBResult($result, $i, 0)) || ('100' == unofficial_getDBResult($result, $i, 0) && !$show_100)) {
            $return .= '
				<OPTION VALUE="' . unofficial_getDBResult($result, $i, 0) . '"';

            /*
                Determine if it's checked
            */

            $val = unofficial_getDBResult($result, $i, 0);

            for ($j = 0; $j < $checked_count; $j++) {
                if ($val == $checked_array[$j]) {
                    $return .= ' SELECTED';
                }
            }

            $return .= '>' . $val . '-' . mb_substr(unofficial_getDBResult($result, $i, 1), 0, 35) . '</OPTION>';
        }
    }

    $return .= '
		</SELECT>';

    return $return;
}

/**
 *    html_build_checkbox() - Render checkbox control
 *
 * @param mixed $name
 * @param mixed $value
 * @param mixed $checked
 * @return html code for checkbox control
 */
function html_build_checkbox($name, $value, $checked)
{
    return '<input type="checkbox" name="' . $name . '"' . ' value="' . $value . '"' . ($checked ? 'checked' : '') . '>';
}

/**
 * get_priority_color() - Wrapper for html_get_priority_color().
 *
 * @param mixed $index
 * @return mixed
 * @return mixed
 * @see    html_get_priority_color()
 */
function get_priority_color($index)
{
    return html_get_priority_color($index);
}

/**
 * html_get_priority_color() - Return the color value for the index that was passed in
 * (defined in $sys_urlroot/themes/<selected theme>/theme.php)
 *
 * @param mixed $index
 * @return mixed
 */
function html_get_priority_color($index)
{
    global $bgpri;

    /* make sure that index is of appropriate type and range */

    $index = (int)$index;

    if ($index < 1) {
        $index = 1;
    } elseif ($index > 9) {
        $index = 9;
    }

    return $bgpri[$index];
}

/**
 * build_priority_select_box() - Wrapper for html_build_priority_select_box()
 *
 * @param mixed $name
 * @param mixed $checked_val
 * @param mixed $nochange
 * @return string
 * @return string
 * @see html_build_priority_select_box()
 */
function build_priority_select_box($name = 'priority', $checked_val = '5', $nochange = false)
{
    return html_build_priority_select_box($name, $checked_val, $nochange);
}

/**
 * html_build_priority_select_box() - Return a select box of standard priorities.
 * The name of this select box is optional and so is the default checked value.
 *
 * @param mixed $name
 * @param mixed $checked_val
 * @param mixed $nochange
 * @return string
 */
function html_build_priority_select_box($name = 'priority', $checked_val = '5', $nochange = false)
{
    $content = '<SELECT NAME="' . $name . '">';

    if ($nochange) {
        $content .= '<OPTION VALUE="100"';

        if ($nochange) {
            $content .= ' SELECTED>No Change</OPTION>';
        } else {
            $content .= '>No Change</OPTION>';
        }
    }

    $content .= '<OPTION VALUE="1"';

    if ('1' == $checked_val) {
        $content .= ' SELECTED';
    }

    $content .= '>1 - ' . _XF_HTM_LOWEST . '</OPTION>';

    $content .= '<OPTION VALUE="2"';

    if ('2' == $checked_val) {
        $content .= ' SELECTED';
    }

    $content .= '>2</OPTION>';

    $content .= '<OPTION VALUE="3"';

    if ('3' == $checked_val) {
        $content .= ' SELECTED';
    }

    $content .= '>3</OPTION>';

    $content .= '<OPTION VALUE="4"';

    if ('4' == $checked_val) {
        $content .= ' SELECTED';
    }

    $content .= '>4</OPTION>';

    $content .= '<OPTION VALUE="5"';

    if ('5' == $checked_val) {
        $content .= ' SELECTED';
    }

    $content .= '>5 - ' . _XF_HTM_MEDIUM . '</OPTION>';

    $content .= '<OPTION VALUE="6"';

    if ('6' == $checked_val) {
        $content .= ' SELECTED';
    }

    $content .= '>6</OPTION>';

    $content .= '<OPTION VALUE="7"';

    if ('7' == $checked_val) {
        $content .= ' SELECTED';
    }

    $content .= '>7</OPTION>';

    $content .= '<OPTION VALUE="8"';

    if ('8' == $checked_val) {
        $content .= ' SELECTED';
    }

    $content .= '>8</OPTION>';

    $content .= '<OPTION VALUE="9"';

    if ('9' == $checked_val) {
        $content .= ' SELECTED';
    }

    $content .= '>9 - ' . _XF_HTM_HIGHEST . '</OPTION>';

    $content .= '</SELECT>';

    return $content;
}

/**
 * html_buildcheckboxarray() - Build an HTML checkbox array.
 *
 * @param mixed $options
 * @param mixed $name
 * @param mixed $checked_array
 * @return string
 */
function html_buildcheckboxarray($options, $name, $checked_array)
{
    $option_count = count($options);

    $checked_count = count($checked_array);

    for ($i = 1; $i <= $option_count; $i++) {
        $content = '
			<BR><INPUT type="checkbox" name="' . $name . '" value="' . $i . '"';

        for ($j = 0; $j < $checked_count; $j++) {
            if ($i == $checked_array[$j]) {
                $content .= ' CHECKED';
            }
        }

        $content .= '> ' . $options[$i];
    }

    return $content;
}
