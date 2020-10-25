<?php

function xoops_module_install_xfmod($module)
{
    $site_specific_config_files = [
        'modules/xfmod/cronjobs/db.php.defaults' => 'modules/xfmod/cronjobs/db.php',
        'modules/xfmod/cronjobs/xoopforge.cron.defaults' => 'modules/xfmod/cronjobs/xoopforge.cron',
        'modules/xfmod/cache/newsletterconfig.php.defaults' => 'modules/xfmod/cache/newsletterconfig.php',
    ];

    echo install_configs($site_specific_config_files);

    echo add_default_user();
}

function add_default_user()
{
    $return = '';

    $hUser = xoops_getHandler('user');

    $user100 = $hUser->get('100');

    if ($user100) {
        $return .= "A user with uid 100 already exists.<br>\n";

        if ('none' == $user100->getVar('uname')) {
            return $return . "The users uname is none. No configuration change needed.<br>\n";
        }  

        $hUser->delete($user100);

        create_user_100();

        $user100->setVar('uid', 0);

        $hUser->insert($user100);

        return $return . 'The user previously with uid 100 has been moved to the end of the list and user none has been added with uid 100';
    }  

    if (create_user_100()) {
        return "Added user none with uid 100 to the database<br>\n";
    }
  

    return "Failed to add user none to the database with uid 100<br>\n";
}

function create_user_100()
{
    $hUser = xoops_getHandler('user');

    $user = $hUser->create();

    $user->setVar('uid', 100);

    $user->setVar('uname', 'none');

    $user->setVar('email', 'none@none.com');

    $user->setVar('pass', '*********34343');

    return $hUser->insert($user);
}

function install_configs($config_files)
{
    $return = '';

    foreach ($config_files as $default_file => $real_file) {
        if (!file_exists($real_file)) {
            if (!file_exists($default_file)) {
                $return .= "Configuration broken - couldn't find $default_file.<br>\n";
            } elseif (!copy($default_file, $real_file)) {
                $return .= "Unable to copy $default_file to $real_file.<br>\n";
            } else {
                $return .= "Copy of $default_file to $real_file was successful.<br>\n";
            }
        }
    }

    return $return;
}
