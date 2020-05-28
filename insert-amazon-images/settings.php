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
        <h3>Licence Key</h3>
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

        <h3>Amazon Product Advertising Api credentials</h3>
        <table class="widefat fixed" cellspacing="0" cellpadding="0">

            <tr valign="top">
                <th scope="row"><label for="<?php echo AmazonImages::class ?>AccessKey">Access Key</label></th>
                <td colspan=3>
                    <input style="width:100%" type="text" name="<?php echo AmazonImages::class ?>AccessKey"
                                     id="<?php echo AmazonImages::class ?>AccessKey"
                                     value="<?php echo \get_option(AmazonImages::class . 'AccessKey'); ?>" required/>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="<?php echo AmazonImages::class ?>SecretKey">Secret Key</label></th>
                <td colspan=3><input style="width:100%" type="password" name="<?php echo AmazonImages::class ?>SecretKey"
                                     id="<?php echo AmazonImages::class ?>SecretKey"
                                     value="<?php echo \get_option(AmazonImages::class . 'SecretKey'); ?>" required/>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="<?php echo AmazonImages::class ?>TagUs">Associate Tag US</label></th>
                <td colspan=3 data-settings-tag="com">
                    <input type="text" name="<?php echo AmazonImages::class ?>TagUs"
                           id="<?php echo AmazonImages::class ?>TagUs"
                           value="<?php echo \get_option(AmazonImages::class . 'TagUs'); ?>"
                           placeholder="XXXXXX-20"
                    />
                    <button type="button" class="button button-primary">Test</button>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="<?php echo AmazonImages::class ?>TagIt">Associate Tag Italy</label></th>
                <td data-settings-tag="it">
                    <input type="text" name="<?php echo AmazonImages::class ?>TagIt"
                           id="<?php echo AmazonImages::class ?>TagIt"
                           value="<?php echo \get_option(AmazonImages::class . 'TagIt'); ?>"
                           placeholder="XXXXXX-21"
                    />
                    <button type="button" class="button button-primary">Test</button>
                </td>
                <th scope="row"><label for="<?php echo AmazonImages::class ?>TagDe">Associate Tag Germany</label></th>
                <td data-settings-tag="de">
                    <input type="text" name="<?php echo AmazonImages::class ?>TagDe"
                           id="<?php echo AmazonImages::class ?>TagDe"
                           value="<?php echo \get_option(AmazonImages::class . 'TagDe'); ?>"
                           placeholder="XXXXXX-21"
                    />
                    <button type="button" class="button button-primary">Test</button>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="<?php echo AmazonImages::class ?>TagUk">Associate Tag United Kingdom</label>
                </th>
                <td data-settings-tag="co.uk">
                    <input type="text" name="<?php echo AmazonImages::class ?>TagUk"
                           id="<?php echo AmazonImages::class ?>TagUk"
                           value="<?php echo \get_option(AmazonImages::class . 'TagUk'); ?>"
                           placeholder="XXXXXX-21"
                    />
                    <button type="button" class="button button-primary">Test</button>
                </td>
                <th scope="row"><label for="<?php echo AmazonImages::class ?>TagEs">Associate Tag Spain</label></th>
                <td data-settings-tag="es">
                    <input type="text" name="<?php echo AmazonImages::class ?>TagEs"
                           id="<?php echo AmazonImages::class ?>TagEs"
                           value="<?php echo \get_option(AmazonImages::class . 'TagEs'); ?>"
                           placeholder="XXXXXX-21"
                    />
                    <button type="button" class="button button-primary">Test</button>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="<?php echo AmazonImages::class ?>TagFr">Associate Tag France</label></th>
                <td data-settings-tag="fr">
                    <input type="text" name="<?php echo AmazonImages::class ?>TagFr"
                           id="<?php echo AmazonImages::class ?>TagFr"
                           value="<?php echo \get_option(AmazonImages::class . 'TagFr'); ?>"
                           placeholder="XXXXXX-21"
                    />
                    <button type="button" class="button button-primary">Test</button>
                </td>
                <th scope="row"><label for="<?php echo AmazonImages::class ?>TagCa">Associate Tag Canada</label></th>
                <td data-settings-tag="ca">
                    <input type="text" name="<?php echo AmazonImages::class ?>TagCa"
                           id="<?php echo AmazonImages::class ?>TagCa"
                           value="<?php echo \get_option(AmazonImages::class . 'TagCa'); ?>"
                           placeholder="XXXXXX-20"
                    />
                    <button type="button" class="button button-primary">Test</button>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="<?php echo AmazonImages::class ?>TagIn">Associate Tag India</label></th>
                <td data-settings-tag="in">
                    <input type="text" name="<?php echo AmazonImages::class ?>TagIn"
                           id="<?php echo AmazonImages::class ?>TagIn"
                           value="<?php echo \get_option(AmazonImages::class . 'TagIn'); ?>"
                           placeholder="XXXXXX-21"
                    />
                    <button type="button" class="button button-primary">Test</button>
                </td>
                <th scope="row"><label for="<?php echo AmazonImages::class ?>TagAu">Associate Tag Australia</label></th>
                <td data-settings-tag="com.au">
                    <input type="text" name="<?php echo AmazonImages::class ?>TagAu"
                           id="<?php echo AmazonImages::class ?>TagAu"
                           value="<?php echo \get_option(AmazonImages::class . 'TagAu'); ?>"
                           placeholder="XXXXXX-20"
                    />
                    <button type="button" class="button button-primary">Test</button>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row"><label for="<?php echo AmazonImages::class ?>NoFollowLinks">No Follow Links ?</label></th>
                <td colspan=3>
			        <?php
			        if(!empty(\get_option(AmazonImages::class.'NoFollowLinks'))){
				        ?>
                        <input type="radio" name="<?php echo AmazonImages::class ?>NoFollowLinks" value="yes" <?php checked( 'yes', \get_option(AmazonImages::class.'NoFollowLinks') ); ?> /> yes
                        <input type="radio" name="<?php echo AmazonImages::class ?>NoFollowLinks" value="no" <?php checked( 'no', \get_option(AmazonImages::class.'NoFollowLinks') ); ?>/> no
				        <?php
			        }
			        else{
				        ?>
                        <input type="radio" name="<?php echo AmazonImages::class ?>NoFollowLinks" value="yes" checked/> yes
                        <input type="radio" name="<?php echo AmazonImages::class ?>NoFollowLinks" value="no"/> no
				        <?php
			        }
			        ?>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row"><label for="<?php echo AmazonImages::class ?>UseGeoip">Use Geolocation ?</label></th>
                <td colspan=3>
                    <?php
                    if(!empty(\get_option(AmazonImages::class.'UseGeoip'))){
                        ?>
                        <input type="radio" name="<?php echo AmazonImages::class ?>UseGeoip" value="yes" <?php checked( 'yes', \get_option(AmazonImages::class.'UseGeoip') ); ?> /> yes
                        <input type="radio" name="<?php echo AmazonImages::class ?>UseGeoip" value="no" <?php checked( 'no', \get_option(AmazonImages::class.'UseGeoip') ); ?>/> no
	                    <?php
                    }
                    else{
                        ?>
                        <input type="radio" name="<?php echo AmazonImages::class ?>UseGeoip" value="yes" checked/> yes
                        <input type="radio" name="<?php echo AmazonImages::class ?>UseGeoip" value="no"/> no
                        <?php
                    }
                    ?>
                </td>
            </tr>
        </table>
        <?php @\submit_button(); ?>
    </form>
    <div data-settings-test-response style="display:none">
    </div>
    <h3>README</h3>
    <table class="widefat fixed" cellspacing="0" cellpadding="0">
        <tr valign="top">
            <td>
                <p>You must have an active Amazon Associates account in order to use AMZ Images. To get started, please paste in your API credentials and associates tag. If you don't know your Amazon API credentials, <a target="_blank" href="https://affiliate-program.amazon.com/assoc_credentials/home">click here to find them.</a></p>
            </td>
        </tr>
    </table>

</div>