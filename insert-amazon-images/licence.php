<?php
namespace AmazonImages;

use Exception;
?>
<div class="wrap">
    <h2>Amazon Images</h2>
    <form method="post" action="options.php">
        <?php
        @\settings_fields('AmazonImages');
        ?>
        <h3>Enter Your License Key</h3>
        <table class="widefat fixed" cellspacing="0" cellpadding="0">

            <tr valign="top">
                <th scope="row"><label for="<?php echo AmazonImages::class ?>Licence">Key</label></th>
                <td colspan=3>
                    <input style="width:100%" type="text" name="<?php echo AmazonImages::class ?>Licence"
                                     id="<?php echo AmazonImages::class ?>Licence"
                                     value="<?php echo \get_option(AmazonImages::class . 'Licence'); ?>" required/>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="<?php echo AmazonImages::class ?>LicenceSecret">Licence Secret Key</label></th>
                <td colspan=3>
                    <input style="width:100%" type="password" name="<?php echo AmazonImages::class ?>LicenceSecret" id="<?php echo AmazonImages::class ?>LicenceSecret" value="<?php echo \get_option(AmazonImages::class . 'LicenceSecret'); ?>" required/>
                </td>
            </tr>
        </table>
        <?php @\submit_button(); ?>
    </form>
</div>