<?php
/**
 * @package  Instant Search Plugin for Zen Cart
 * @author   marco-pm
 * @version  3.0.1
 * @see      https://github.com/marco-pm/zencart_instantsearch
 * @license  GNU Public License V2.0
 */

if (defined('INSTANT_SEARCH_DROPDOWN_ENABLED') && INSTANT_SEARCH_DROPDOWN_ENABLED === 'true') { ?>
    <script>
        const instantSearchSecurityToken          = '<?php echo $_SESSION['securityToken']; ?>';
        const instantSearchDropdownInputWaitTime  = parseInt(<?php echo INSTANT_SEARCH_DROPDOWN_INPUT_WAIT_TIME; ?>);
        const instantSearchDropdownInputMinLength = parseInt(<?php echo INSTANT_SEARCH_DROPDOWN_MIN_WORDSEARCH_LENGTH; ?>);
        const instantSearchDropdownInputSelector  = '<?php echo str_replace("'", "\'", INSTANT_SEARCH_DROPDOWN_INPUT_BOX_SELECTOR); ?>:not([type=hidden])';
    </script>
<?php }
