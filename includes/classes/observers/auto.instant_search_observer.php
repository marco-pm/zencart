<?php

class zcObserverInstantSearchObserver extends base
{
    public function __construct()
    {
        $this->attach($this, [
            'NOTIFY_FOOTER_END'
        ]);
    }

    public function updateNotifyFooterEnd(&$class, $eventID, $paramsArray)
    {
        if (defined('INSTANT_SEARCH_DROPDOWN_ENABLED') && INSTANT_SEARCH_DROPDOWN_ENABLED === 'true') {
            echo "<script src=\"" . DIR_WS_TEMPLATE . "jscript/" . "instant_search_dropdown.min.js\"></script>";
        }

        if (defined('INSTANT_SEARCH_PAGE_ENABLED') && INSTANT_SEARCH_PAGE_ENABLED === 'true') {
            $instantSearchZcSearchResultPageName = zen_get_zcversion() >= '1.5.8' ? FILENAME_SEARCH_RESULT : FILENAME_ADVANCED_SEARCH_RESULT;
            $instantSearchFormSelector = "form[action*=${instantSearchZcSearchResultPageName}]:not([name=search]):not([name=advanced_search])";

            // Replace the search forms' action"
            echo "
            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    // Replace the search forms' action
                    const instantSearchFormPageInputs = document.querySelectorAll(`{$instantSearchFormSelector} input[value=\"{$instantSearchZcSearchResultPageName}\"]`);
                    instantSearchFormPageInputs.forEach(input => input.value = \"" . FILENAME_INSTANT_SEARCH_RESULT . "\");

                    const instantSearchFormSearchDescrInputs = document.querySelectorAll(`{$instantSearchFormSelector} input[name=\"search_in_description\"]`);
                    instantSearchFormSearchDescrInputs.forEach(input => input.remove());

                    const instantSearchForms = document.querySelectorAll(`{$instantSearchFormSelector}`);
                    instantSearchForms.forEach(form => form.action = form.action.replace('{$instantSearchZcSearchResultPageName}', '" . FILENAME_INSTANT_SEARCH_RESULT . "'));
                });
            </script>
            ";
        }
    }
}
