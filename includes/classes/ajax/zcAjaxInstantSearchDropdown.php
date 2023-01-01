<?php
/**
 * @package  Instant Search Plugin for Zen Cart
 * @author   marco-pm
 * @version  3.0.0
 * @see      https://github.com/marco-pm/zencart_instantsearch
 * @license  GNU Public License V2.0
 */

declare(strict_types=1);

use Zencart\Plugins\Catalog\InstantSearch\InstantSearch;

class zcAjaxInstantSearchDropdown extends InstantSearch
{
    /**
     * Array of allowed search fields (keys) for building the sql sequence by calling the
     * corresponding sql build method(s) with parameters (values).
     *
     * @var array
     */
    protected const VALID_SEARCH_FIELDS = [
        'model-exact' => [
            ['buildSqlProductModel', [true]],
        ],
        'name' => [
            ['buildSqlProductName', [true]],
            ['buildSqlProductNameDescriptionMatch', [false, INSTANT_SEARCH_DROPDOWN_USE_QUERY_EXPANSION === 'true']],
            ['buildSqlProductName', [false]],
        ],
        'name-description' => [
            ['buildSqlProductName', [true]],
            ['buildSqlProductNameDescriptionMatch', [true, INSTANT_SEARCH_DROPDOWN_USE_QUERY_EXPANSION === 'true']],
            ['buildSqlProductName', [false]],
        ],
        'model-broad' => [
            ['buildSqlProductModel', [false]],
        ],
        'category' => [
            ['buildSqlCategory'],
        ],
        'manufacturer' => [
            ['buildSqlManufacturer'],
        ],
    ];

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * AJAX-callable method that performs the search on $_POST['keyword'] and returns the results in HTML format.
     *
     * @return string HTML-formatted results
     */
    public function instantSearch(): string
    {
        if (!isset($_POST['keyword'])) {
            return '';
        }

        return $this->performSearch($_POST['keyword']);
    }

    /**
     * Returns the exploded fields list setting and the error message to show in case of error while
     * parsing the list.
     *
     * @return array First element: search fields array; second element: error message
     */
    protected function loadSearchFieldsConfiguration(): array
    {
        $searchFields = explode(',', preg_replace('/,$/', '', str_replace(' ', '', INSTANT_SEARCH_DROPDOWN_FIELDS_LIST))); // Remove spaces and extra comma at the end
        $errorMessage = sprintf(TEXT_INSTANT_SEARCH_CONFIGURATION_ERROR, 'INSTANT_SEARCH_DROPDOWN_FIELDS_LIST');

        return [$searchFields, $errorMessage];
    }

    /**
     * Sanitizes the input query, runs the search and formats the results.
     *
     * @param string $inputQuery The search query
     * @return string HTML-formatted results
     */
    protected function performSearch(string $inputQuery): string
    {
        $this->searchQuery = html_entity_decode(strtolower(strip_tags(trim($inputQuery))), ENT_NOQUOTES, 'utf-8');
        $searchQueryLength = strlen($this->searchQuery);

        if ($this->searchQuery !== '' &&
            $searchQueryLength >= INSTANT_SEARCH_DROPDOWN_MIN_WORDSEARCH_LENGTH &&
            $searchQueryLength <= INSTANT_SEARCH_DROPDOWN_MAX_WORDSEARCH_LENGTH
        ) {
            return parent::performSearch($inputQuery);
        }

        return '';
    }

    /**
     * Returns the search results formatted with the template.
     *
     * @return string HTML output with the formatted results.
     */
    protected function formatResults(): string
    {
        global $template;

        $dropdownResults = [];

        foreach ($this->results as $result) {
            if (!empty($result['products_id'])) {
                $id    = $result['products_id'];
                $name  = zen_get_products_name($id);
                $img   = $result['products_image'];
                $model = zen_get_products_model($id);

                $dropdownResult['link']  = zen_href_link(zen_get_info_page($id), 'products_id=' . $id);
                $dropdownResult['model'] = INSTANT_SEARCH_DROPDOWN_DISPLAY_PRODUCT_MODEL === 'true'
                    ? (INSTANT_SEARCH_DROPDOWN_INCLUDE_PRODUCT_MODEL === 'true' ? $this->highlightSearchWords($model) : $model)
                    : '';
                $dropdownResult['price'] = INSTANT_SEARCH_DROPDOWN_DISPLAY_PRODUCT_PRICE === 'true'
                    ? zen_get_products_display_price($id)
                    : '';
            } elseif (!empty($result['categories_id'])) {
                $id    = $result['categories_id'];
                $name  = $result['categories_name'];
                $img   = $result['categories_image'];

                $dropdownResult['link']  = zen_href_link(FILENAME_DEFAULT, 'cPath=' . $id);
                $dropdownResult['count'] = INSTANT_SEARCH_DROPDOWN_DISPLAY_CATEGORIES_COUNT === 'true'
                    ? zen_count_products_in_category($id)
                    : '';
            } elseif (!empty($result['manufacturers_id'])) {
                $id    = $result['manufacturers_id'];
                $name  = $result['manufacturers_name'];
                $img   = $result['manufacturers_image'];

                $dropdownResult['link']  = zen_href_link(FILENAME_DEFAULT, 'manufacturers_id=' . $id);
                $dropdownResult['count'] = INSTANT_SEARCH_DROPDOWN_DISPLAY_MANUFACTURERS_COUNT === 'true'
                    ? zen_count_products_for_manufacturer((int)$id)
                    : '';
            } else {
                continue;
            }

            $dropdownResult['id']   = (int)$id;
            $dropdownResult['name'] = $this->highlightSearchWords(strip_tags($name));
            $dropdownResult['img'] = INSTANT_SEARCH_DROPDOWN_DISPLAY_IMAGE === 'true' && $img !== ''
                ? zen_image(DIR_WS_IMAGES . strip_tags($img), strip_tags($img), INSTANT_SEARCH_DROPDOWN_IMAGE_WIDTH, INSTANT_SEARCH_DROPDOWN_IMAGE_HEIGHT)
                : '';

            $this->notify('NOTIFY_INSTANT_SEARCH_DROPDOWN_ADD_DROPDOWN_RESULT', $result, $dropdownResult);

            $dropdownResults[] = $dropdownResult;
        }

        ob_start();
        require $template->get_template_dir('tpl_ajax_instant_search_results_dropdown.php', DIR_WS_TEMPLATE, FILENAME_DEFAULT, 'templates') . '/tpl_ajax_instant_search_results_dropdown.php';
        return ob_get_clean();
    }

    /**
     * Calculate the sql LIMIT value based on the max number of results allowed and the
     * number of results found so far.
     *
     * @return int LIMIT value
     */
    protected function calcResultsLimit(): int
    {
        // $resultsLimit = ((int)MAX_DISPLAY_PRODUCTS_LISTING * $this->resultPage) - count($this->results);
        return (int)INSTANT_SEARCH_DROPDOWN_MAX_RESULTS - count($this->results);
    }

    /**
     * Highlights in bold the tokens/suggestions in the results.
     *
     * @param string $text Input string
     * @return string Output string
     */
    protected function highlightSearchWords(string $text): string
    {
        if (INSTANT_SEARCH_DROPDOWN_HIGHLIGHT_TEXT === 'none') {
            return $text;
        }

        return preg_replace('/(' . str_replace('/', '\/', $this->searchQueryRegexp) . ')/i', '<span>$1</span>', $text);
    }
}
