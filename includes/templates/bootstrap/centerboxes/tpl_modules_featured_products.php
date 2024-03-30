<?php
/**
 * Module Template
 * 
 * BOOTSTRAP v3.6.4
 *
 * @copyright Copyright 2003-2005 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: tpl_modules_featured_products.php 2935 2006-02-01 11:12:40Z birdbrain $
 */
$zc_show_featured = false;
require DIR_WS_MODULES . zen_get_module_directory('centerboxes/' . FILENAME_FEATURED_PRODUCTS_MODULE);

if ($zc_show_featured === false) {
    return;
}

$card_main_id = 'featuredProducts';
$card_main_class = 'centerBoxWrapper';
$card_body_id = 'featuredCenterbox-card-body';
?>
<!-- bof: featured products  -->
<?php
require $template->get_template_dir('tpl_columnar_display.php', DIR_WS_TEMPLATE, $current_page_base, 'common') . '/tpl_columnar_display.php';
?>
<!-- eof: featured products  -->
