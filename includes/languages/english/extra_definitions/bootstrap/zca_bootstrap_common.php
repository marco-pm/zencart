<?php
/*
 * BOOTSTRAP v3.6.4
 */
// -----
// Part of the Bootstrap template, defining commonly-used phrases and phrases unique to the bootstrap template.
//
define('BOOTSTRAP_PLEASE_SELECT', 'Please select ...');
define('BOOTSTRAP_CURRENT_ADDRESS', ' (Currently Selected)');

// -----
// Additional buttons.
//
define('BUTTON_BACK_TO_TOP_TITLE', 'Back to top');

// -----
// Used on the products_all and product listing for the alpha-filter dropdown.
// Note: Defined in multiple language files for zc158 and zc200!
//
define('TEXT_SHOW', 'Filter by:');

// -----
// Used during checkout and on various address-rendering pages.
//
define('TEXT_SELECT_OTHER_PAYMENT_DESTINATION', 'Please select the preferred billing address if the invoice to this order is to be delivered elsewhere.');
define('TEXT_SELECT_OTHER_SHIPPING_DESTINATION', 'Please select the preferred shipping address if this order is to be delivered elsewhere.');
define('NEW_ADDRESS_TITLE', 'Enter new address');
define('TEXT_PRIMARY_ADDRESS', ' (Primary Address)');
define('PRIMARY_ADDRESS', ' (Primary Address)');
define('TABLE_HEADING_ADDRESS_BOOK_ENTRIES', 'Choose From Your Address Book Entries');

// -----
// Used on the product*_info pages.
//
define('TEXT_MULTIPLE_IMAGES', ' Additional Images ');
define('TEXT_SINGLE_IMAGE', ' Larger Image ');
define('PREV_NEXT_FROM', ' from ');
define('IMAGE_BUTTON_PREVIOUS', 'Previous Item');
define('IMAGE_BUTTON_NEXT', 'Next Item');
define('IMAGE_BUTTON_RETURN_TO_PRODUCT_LIST', 'Back to Product List');
define('MODAL_ADDL_IMAGE_PLACEHOLDER_ALT', 'Modal Additional Images for %s');   //- %s is filled in with the product's name

// -----
// Used on the product_music_info page.
//
define('TEXT_ARTIST_URL', 'For more information, please visit the Artist\'s <a href="%s" target="_blank">webpage</a>.');
define('TEXT_PRODUCT_RECORD_COMPANY', 'Record Company: ');

// -----
// Used on the shopping_cart page.
//
define('TEXT_CART_MODAL_HELP', '[help (?)]');
define('HEADING_TITLE_CART_MODAL', 'Visitors Cart / Members Cart');

// -----
// Used on various pages that display the cart's contents.
//
define('SUB_TITLE_TOTAL', 'Total:');

// -----
// Used by various product listing pages, e.g. SNAF.
//
// -----
// The two image-heading constants are used when a site chooses to display listings
// in table-mode (PRODUCT_LISTING_COLUMNS_PER_ROW is set to '1').  If your site wants
// the image-heading to *always* be displayed, set the TABLE_HEADING_IMAGE value to
// the text you desire.  If that value is set to an empty string, then a screen-reader-only
// heading is used along with the TABLE_HEADING_IMAGE_SCREENREADER value.
//
define('TABLE_HEADING_IMAGE', '');
define('TABLE_HEADING_IMAGE_SCREENREADER', 'Product Image');

define('TABLE_HEADING_PRODUCTS', 'Product Name');
define('TABLE_HEADING_MANUFACTURER', 'Manufacturer');
define('TABLE_HEADING_PRICE', 'Price');
define('TABLE_HEADING_WEIGHT', 'Weight');
define('TABLE_HEADING_BUY_NOW', 'Buy Now');
define('TEXT_NO_PRODUCTS', 'There are no products to list in this category.');
define('TEXT_NO_PRODUCTS2', 'There is no product available from this manufacturer.');

// -----
// Used by various /modalboxes
//
define('TEXT_MODAL_CLOSE', 'Close');
define('TEXT_MORE_INFO', '[More Info]');
define('ARIA_REVIEW_STAR', 'star');
define('ARIA_REVIEW_STARS', 'stars');

// -----
// Overriding definition for the login page, removing unwanted javascript.
//
define('TEXT_VISITORS_CART', '<strong>Note:</strong> If you have shopped with us before and left something in your cart, for your convenience, the contents will be merged if you log back in.');

// -----
// Used by the (optional) AJAX search feature.
//
define('TEXT_AJAX_SEARCH_TITLE', 'What can we help you find?');
define('TEXT_AJAX_SEARCH_PLACEHOLDER', 'Search here...');
define('TEXT_AJAX_SEARCH_RESULTS', 'Total %u results found.');
define('TEXT_AJAX_SEARCH_VIEW_ALL', 'View All');

// -----
// ARIA label text, used in the common header.
//
define('TEXT_HEADER_ARIA_LABEL_NAVBAR', 'Navigation Bar');
define('TEXT_HEADER_ARIA_LABEL_LOGO', 'Site Logo');

// -----
// ARIA label text, used by /sideboxes/tpl_orders_history.php.
//
// NOTE: Not replicated in lang.zca_bootstrap_common.php, since this constant is
// defined in lang.english.php for zc158 and later.
//
define('PAGE_ACCOUNT_HISTORY', 'Order History');

// -----
// <h1> text for index pages where the 'normal' heading-text isn't provided by a
// store ... for accessibility.
//
// Note: For zc200, this constant will be in /includes/languages/english/lang.index.php.
//
define('HEADING_TITLE_SCREENREADER', 'See Additional Content Below');
