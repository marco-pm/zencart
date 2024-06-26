<?php
/**
 * @copyright Copyright 2003-2023 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: pRose on charmes 2022 Dec 03 Modified in v1.5.8a $
 */

if (!zen_is_superuser() && !check_page(FILENAME_ORDERS, '')) return;

// to disable this module for everyone, uncomment the following "return" statement so the rest of this file is ignored
// return;

?>
<div class="panel panel-default reportBox">
    <div class="panel-heading header"><?php echo BOX_TITLE_ORDERS; ?> </div>
    <table class="table table-striped table-condensed">
        <?php
        $orders_status = $db->Execute("SELECT orders_status_name, orders_status_id FROM " . TABLE_ORDERS_STATUS . " WHERE language_id = " . (int)$_SESSION['languages_id'] . " ORDER BY sort_order ASC, orders_status_id ASC", false, true, 3600);

        foreach ($orders_status as $row) {
          $orders_pending = $db->Execute("SELECT count(*) as count FROM " . TABLE_ORDERS . " WHERE orders_status = " . (int)$row['orders_status_id'], false, true, 1800);
          ?>
        <tr>
          <td><a href="<?php echo zen_href_link(FILENAME_ORDERS, 'statusFilterSelect=' . $row['orders_status_id']); ?>"><?php echo $row['orders_status_name']; ?></a>:</td>
          <td class="text-right"> <?php echo $orders_pending->fields['count']; ?></td>
        </tr>
        <?php
      }
      ?>
    </table>
</div>
