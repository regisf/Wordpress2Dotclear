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
?>
<html>
    <head>
        <title><?php echo(__('Wordpress 2 Dotclear')); ?></title>

        <style>
            #rotation { display: none; }
        </style>
        <script type="text/javascript">
            $(document).ready(function() {
                if (window.FileReader && window.File && window.FileList && window.Blob) {
                    $('form#wp2dcform').delegate($("#wordpressxml"), 'change', function(evt) {
                        var files = evt.target.files;
                        for (var i=0,f; f = files[i]; i++) {
                            if (f.type != 'text/xml') {
                                alert("This is not a XML file");
                                $("#wordpressxml").val('');
                                return;
                            }
                        }
                    });
                }
            });
        </script>
    </head>
    <body>
        <h2><?php echo(__('Wordpress 2 Dotclear')); ?></h2>

        <?php if ( ! empty($msg)): ?>
            <p class="message"><?php echo $msg; ?></p>
        <?php endif; ?>

        <div>
            <?php echo(__('To migrate from Wordpress to Dotclear, please, send the Wordpress XML file (Tools -> Export -> Download export file)')); ?>
        </div>

        <div>
            <form method="post" action="<?php echo($p_url); ?>" enctype="multipart/form-data" id="wp2dcform">
                <?php $core->formNonce(); ?>
                <label class="classic"><?php echo(__('Your Wordpress file:')); ?></label>
                <input type="file" name="<?php echo WORDPRESSXML; ?>" id="<?php echo WORDPRESSXML; ?>" accept="text/xml" /> <input type="submit" value="<?php echo(__("Send")); ?>" />
                <img src="<?php echo join(DIRECTORY_SEPARATOR, array('admin', 'plugins', 'wp2dc' , 'images', 'rotation.png')); ?>" >
            </form>
        </div>
    </body>
</html>
