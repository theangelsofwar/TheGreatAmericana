<?php

namespace AmazonImages;

$shortname=(new \ReflectionClass(AmazonImages::class))->getShortName();
?>
<div id="insert-amazon-images-thickbox" style="display:none;overflow:scroll">
    <div>
        <?php
        $table_class='form-table';
        include('add_amazon_images_form.php');
        ?>
    </div>
</div>
<?php
include('add_amazon_images_templates.php');
?>