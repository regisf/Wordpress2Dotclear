<?php if ( ! defined('DC_CONTEXT_ADMIN')) { return; }

$_menu['Plugins']->addItem(
    __('Wordpress2Dotclear'),
    'plugin.php?p=wp2dc',
    'index.php?pf=wp2dc/icon.png',
    preg_match('/plugin\.php\?p=wp2dc(&.*)?$/', $_SERVER['REQUEST_URI']),
    $core->auth->check('contentadmin',$core->blog->id)
);

?>
