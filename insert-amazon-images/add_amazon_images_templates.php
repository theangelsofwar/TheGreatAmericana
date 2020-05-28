<?php
namespace AmazonImages;

?>
<template id="<?php echo $shortname?>_search_result_template">
    <div style="padding:20px">
        <form>
        <table class="widefat">
            <thead>
            <tr><th colspan="2"><h4 data-title></h4></th></tr>
            </thead>
            <tbody>
            <tr>
                <td style="width:160px">
                    <a target="_blank" data-url href="" title=""><img data-primary-image data-small-image="" data-large-image="" src="" alt=""></a>
                </td>
                <td data-images style="vertical-align:top;text-align: left;">

                </td>
            </tr>
            <tr>
                <td style="width:160px;text-align:center">
                    <button type="button" class="button button-primary" data-type="<?php echo $data_type_iframe?'iframe':''?>">Insert Into Post</button>
                </td>
                <td style="text-align: left">
                    <table cellpadding="0" cellspacing="0">
                        <tr>
                            <td>
                                <strong>Choose image size:</strong>
                                <input type="radio" name="selected_image" value="small"> small
                                <input type="radio" name="selected_image" value="medium"> medium
                                <input type="radio" name="selected_image" value="large"> large

                            </td>
                        </tr>
                        <tr>
                            <td>
                                <strong data-price-row>Price: <span data-price></span></strong>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            </tbody>
        </table>
            </form>
    </div>
</template>

<template id="<?php echo $shortname?>_search_result_image_template">
    <img src="" alt="" title="" data-medium-image="" data-large-image="" onmouseover="this.style.cursor='pointer'">
</template>


<template id="<?php echo $shortname?>_search_result_error_template">
    <h4 data-error></h4>
</template>


<template id="<?php echo $shortname?>_link_template" data-use-nofollow="<?php echo \get_option(AmazonImages::class.'NoFollowLinks');?>">
    <a data-amazonimages="" target="_blank" title="" href=""><img class="" src="" alt="" /></a>
</template>