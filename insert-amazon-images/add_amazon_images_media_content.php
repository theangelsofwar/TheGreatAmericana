<?php

namespace AmazonImages;

$shortname=(new \ReflectionClass(AmazonImages::class))->getShortName();
$data_type_iframe=true;
$table_class='widefat';
include('add_amazon_images_form.php');
include('add_amazon_images_templates.php');
?>