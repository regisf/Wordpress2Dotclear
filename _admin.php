<?php
# -- BEGIN LICENSE BLOCK ---------------------------------------
#
# Wordpress importer for Dotclear
#
# Copyright (c) 2013 Régis FLORET
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK -----------------------------------------

if ( ! defined('DC_CONTEXT_ADMIN')) { return; }

$_menu['Plugins']->addItem(
    __('Wordpress2Dotclear'),
    'plugin.php?p=wp2dc',
    'index.php?pf=wp2dc/icon.png',
    preg_match('/plugin\.php\?p=wp2dc(&.*)?$/', $_SERVER['REQUEST_URI']),
    $core->auth->check('contentadmin',$core->blog->id)
);

?>
