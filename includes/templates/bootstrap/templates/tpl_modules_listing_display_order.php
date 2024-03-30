<?php
/**
 * Module Template
 * 
 * BOOTSTRAP 3.6.3
 *
 * @package templateSystem
 * @copyright Copyright 2003-2006 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: tpl_modules_listing_display_order.php 3369 2006-04-03 23:09:13Z drbyte $
 */
$disp_order = (int)($disp_order ?? 0);
if ($disp_order <= 0 || $disp_order > 8) {
    $disp_order = 8;
}

// -----
// Language constant, added in v200, define here if not previously defined; can be
// removed once support for ZC versions < 2.0.0 is dropped.
//
if (!defined('TEXT_INFO_SORT_BY_RECOMMENDED')) {
    define('TEXT_INFO_SORT_BY_RECOMMENDED', 'Recommended');
}
// NOTE: to remove a sort order option add a PHP comment around the option to be removed
$display_order_options = [
    ['id' => '8', 'text' => TEXT_INFO_SORT_BY_RECOMMENDED],
    ['id' => '1', 'text' => TEXT_INFO_SORT_BY_PRODUCTS_NAME],
    ['id' => '2', 'text' => TEXT_INFO_SORT_BY_PRODUCTS_NAME_DESC],
    ['id' => '3', 'text' => TEXT_INFO_SORT_BY_PRODUCTS_PRICE],
    ['id' => '4', 'text' => TEXT_INFO_SORT_BY_PRODUCTS_PRICE_DESC],
    ['id' => '5', 'text' => TEXT_INFO_SORT_BY_PRODUCTS_MODEL],
    ['id' => '6', 'text' => TEXT_INFO_SORT_BY_PRODUCTS_DATE_DESC],
    ['id' => '7', 'text' => TEXT_INFO_SORT_BY_PRODUCTS_DATE],
];
?>
<div id="listingDisplayOrderSorter" class="row mb-3">
    <label for="disp-order-sorter" class="mx-2"><?php echo TEXT_INFO_SORT_BY; ?></label>
<?php
echo
    zen_draw_form('sorter_form', zen_href_link($_GET['main_page']), 'get', 'class="form-inline"') .
    zen_draw_hidden_field('main_page', $_GET['main_page']) .
    zen_hide_session_id();
if (isset($_GET['cPath'], $cPath)) {
    echo zen_draw_hidden_field('cPath', $cPath);
}

echo
    zen_draw_pull_down_menu('disp_order', $display_order_options, $disp_order, 'id="disp-order-sorter" onchange="this.form.submit();"') .
    '</form>';
?>
</div>
