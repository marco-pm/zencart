<?php

class zcObserverInstantSearchObserver extends base
{
    public function __construct()
    {
        $this->attach($this, [
            'NOTIFY_ZEN_DRAW_INPUT_FIELD',
            'NOTIFY_FOOTER_END',
        ]);
    }

    public function updateNotifyZenDrawInputField(&$class, $eventID, $paramsArray, &$field)
    {
        if (defined('INSTANT_SEARCH_DROPDOWN_ENABLED') &&
            INSTANT_SEARCH_DROPDOWN_ENABLED === 'true' &&
            !empty($paramsArray['name']) &&
            $paramsArray['name'] === 'keyword'
        ) {
            $field = '<div class="instantSearchInputWrapper">' . $field . '</div>';
        }
    }

    public function updateNotifyFooterEnd(&$class, $eventID, $paramsArray)
    {
        if (defined('INSTANT_SEARCH_DROPDOWN_ENABLED') &&
            INSTANT_SEARCH_DROPDOWN_ENABLED === 'true'
        ) {
            echo "<script src=\"" . DIR_WS_TEMPLATE . "jscript/" . "instant_search_dropdown.min.js\"></script>";
        }
    }
}
