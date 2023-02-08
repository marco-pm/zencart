<?php
/**
 * @package  Typesense Plugin for Zen Cart
 * @author   marco-pm
 * @version  1.0.0
 * @see      https://github.com/marco-pm/zencart_typesense
 * @license  GNU Public License V2.0
 */

declare(strict_types=1);

chdir(__DIR__);
$loaderPrefix = 'instantsearch_typesense_cron';
$_SERVER['REMOTE_ADDR'] = 'cron';
$_SERVER['REQUEST_URI'] = 'cron';
$result = require('includes/application_top.php');
$_SERVER['HTTP_USER_AGENT'] = 'Zen Cart update';

use Zencart\Plugins\Catalog\InstantSearch\InstantSearchLogger;
use Zencart\Plugins\Catalog\Typesense\TypesenseZencart;

$logger = new InstantSearchLogger('typesense-cron');

/*for ($i = 181; $i < 10000; $i++) {
    $sql = "INSERT INTO products (products_id, products_type, products_quantity, products_model, products_image, products_price, products_virtual, products_date_added, products_last_modified, products_date_available, products_weight, products_status, products_tax_class_id, manufacturers_id, products_ordered, products_quantity_order_min, products_quantity_order_units, products_priced_by_attribute, product_is_free, product_is_call, products_quantity_mixed, product_is_always_free_shipping, products_qty_box_status, products_quantity_order_max, products_sort_order, products_discount_type, products_discount_type_from, products_price_sorter, master_categories_id, products_mixed_discount_quantity)
    VALUES (" . $i . ", '1', '31', 'MG200MMS', 'matrox/mg200mms.gif', '299.9900', 0, '2003-11-03 12:32:17', '2004-04-26 23:57:34', NULL, '23.00', 1, 1, 1, '1', '1', '1', 0, 0, 0, 0, 0, 1, '0', 0, 0, 0, '299.9900', 4, 1)";
    $db->Execute($sql);

    $sql = "INSERT INTO products_description (products_id, language_id, products_name, products_description, products_url)
    VALUES (" . $i . ", 1, 'Matrox G200 MMS', 'Reinforcing its position as a multi-monitor trailblazer, Matrox Graphics Inc. has once again developed the most flexible and highly advanced solution in the industry. Introducing the new Matrox G200 Multi-Monitor Series; the first graphics card ever to support up to four DVI digital flat panel displays on a single 8&quot; PCI board.<br /><br />With continuing demand for digital flat panels in the financial workplace, the Matrox G200 MMS is the ultimate in flexible solutions. The Matrox G200 MMS also supports the new digital video interface (DVI) created by the Digital Display Working Group (DDWG) designed to ease the adoption of digital flat panels. Other configurations include composite video capture ability and onboard TV tuner, making the Matrox G200 MMS the complete solution for business needs.<br /><br />Based on the award-winning MGA-G200 graphics chip, the Matrox G200 Multi-Monitor Series provides superior 2D/3D graphics acceleration to meet the demanding needs of business applications such as real-time stock quotes (Versus), live video feeds (Reuters & Bloombergs), multiple windows applications, word processing, spreadsheets and CAD.', 'www.matrox.com/mga/products/g200_mms/home.cfm')";
    $db->Execute($sql);
    $sql = "INSERT INTO products_description (products_id, language_id, products_name, products_description, products_url)
    VALUES (" . $i . ", 2, 'Matrox G200 MMS', 'Reinforcing its position as a multi-monitor trailblazer, Matrox Graphics Inc. has once again developed the most flexible and highly advanced solution in the industry. Introducing the new Matrox G200 Multi-Monitor Series; the first graphics card ever to support up to four DVI digital flat panel displays on a single 8&quot; PCI board.<br /><br />With continuing demand for digital flat panels in the financial workplace, the Matrox G200 MMS is the ultimate in flexible solutions. The Matrox G200 MMS also supports the new digital video interface (DVI) created by the Digital Display Working Group (DDWG) designed to ease the adoption of digital flat panels. Other configurations include composite video capture ability and onboard TV tuner, making the Matrox G200 MMS the complete solution for business needs.<br /><br />Based on the award-winning MGA-G200 graphics chip, the Matrox G200 Multi-Monitor Series provides superior 2D/3D graphics acceleration to meet the demanding needs of business applications such as real-time stock quotes (Versus), live video feeds (Reuters & Bloombergs), multiple windows applications, word processing, spreadsheets and CAD.', 'www.matrox.com/mga/products/g200_mms/home.cfm')";
    $db->Execute($sql);

    $sql = "INSERT INTO products_to_categories (products_id, categories_id) VALUES (" . $i . ", 4)";
    $db->Execute($sql);
}

exit();*/

try {
    $typesense = new TypesenseZencart();
} catch (Exception $e) {
    $logger->writeErrorLog('TypesenseInstantSearch init error, exiting', $e);
    exit();
}

try {
    $typesense->syncFull();
} catch (Exception|\Http\Client\Exception $e) {
    $logger->writeErrorLog("Error while full-syncing the collections, exiting", $e);
    exit();
}

$client = $typesense->getClient();

try {
    $suca = $client->collections->retrieve();
} catch (Exception|\Http\Client\Exception $e) {
    $logger->writeErrorLog("Error while retrieving collections, exiting", $e);
    exit();
}

$a = 1;

/* $key = $client->keys->create([
    'description' => 'Admin key.',
    'actions' => ['*'],
    'collections' => ['*']
]);*/
