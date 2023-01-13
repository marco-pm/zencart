<?php
/**
 * Adaptation of tpl_search_result_default.php (bootstrap template version)
 * for the Instant Search result page.
 *
 * @package  Instant Search Plugin for Zen Cart
 * @author   marco-pm
 * @version  3.0.1
 * @see      https://github.com/marco-pm/zencart_instantsearch
 * @license  GNU Public License V2.0
 */
?>

<div class="centerColumn" id="instantSearchResultDefault">

    <h1 id="searchResultsDefaultHeading"><?php echo HEADING_TITLE; ?></h1>

    <?php if ($do_filter_list || PRODUCT_LIST_ALPHA_SORTER === 'true') { ?>
        <?php echo zen_draw_form('filter', zen_href_link($search_result_page), 'get') . zen_post_all_get_params(['currency', 'alpha_filter_id']); ?>
        <div class="instantSearchResults__sorterRow my-3">
            <?php require DIR_WS_MODULES . zen_get_module_directory(FILENAME_PRODUCT_LISTING_ALPHA_SORTER); ?>
        </div>
        <?php echo '</form>'; ?>
    <?php } ?>

    <div id="instantSearchResults__noResultsFoundWrapper">
        <?php echo TEXT_NO_PRODUCTS_FOUND; ?>
    </div>

    <div id="productListing" class="group">
    </div>

    <div id="instantSearchResults__loadingWrapper">
        <?php echo TEXT_LOADING_RESULTS; ?>
        <div class="spinner"></div>
    </div>

    <?php // don't remove this div ?>
    <div id="instantSearchResults__end"></div>

</div>
