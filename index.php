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

if (!defined('DC_CONTEXT_ADMIN')) { return; }
    $wp2dc = new wp2dc($core);
    if ($wp2dc->isPostCorrect()) {
        $filePath = $wp2dc->uploadFile();
        if ($filePath) {
            $wp2dc->processFile($filePath);
            $msg = __('Everything goes fine. Your Wordpress blog is now a Dotclear blog. Enjoy.');
        } else {
            $core->error->add($wp2dc->error());
        }
    }

    # Include the template
    include(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'index.php');
?>
