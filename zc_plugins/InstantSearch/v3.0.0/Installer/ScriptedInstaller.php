<?php
/**
 * @package  Instant Search Plugin for Zen Cart
 * @author   marco-pm
 * @version  3.0.0
 * @see      https://github.com/marco-pm/zencart_instantsearch
 * @license  GNU Public License V2.0
 */

use Zencart\PluginSupport\ScriptedInstaller as ScriptedInstallBase;

class ScriptedInstaller extends ScriptedInstallBase
{
    /**
     * Configuration group title.
     *
     * @var string
     */
    protected const CONFIGURATION_GROUP_TITLE = 'Instant Search';

    /**
     * Upgrade the plugin to the new version.
     *
     * @param string $oldVersion
     * @return bool
     */
    public function doUpgrade(string $oldVersion = ''): bool
    {
        if ($oldVersion === '') {
            return false;
        }

        // Get configuration group
        $sql = $this->dbConn->Execute("
            SELECT configuration_group_id
            FROM " . TABLE_CONFIGURATION_GROUP . "
            WHERE configuration_group_title = '" . self::CONFIGURATION_GROUP_TITLE . "'
            LIMIT 1
        ");
        if ($sql->RecordCount() < 0) {
            return false;
        }
        $configurationGroupId = (int)$sql->fields['configuration_group_id'];

        $this->createIndexes();

        $this->createConfigurationSettings($configurationGroupId);

        $this->restorePreviousConfigurationValues($configurationGroupId, $oldVersion);

        return true;
    }

    /**
     * Install the plugin for the first time.
     *
     * @return bool
     */
    public function doInstall(): bool
    {
        $this->createIndexes();

        // Add configuration group, if not already present
        $sql = $this->dbConn->Execute("
            SELECT configuration_group_id
            FROM " . TABLE_CONFIGURATION_GROUP . "
            WHERE configuration_group_title = '" . self::CONFIGURATION_GROUP_TITLE . "'
            LIMIT 1
        ");
        if ($sql->RecordCount < 0) {
            $this->dbConn->Execute("
                INSERT INTO " . TABLE_CONFIGURATION_GROUP . " (configuration_group_title, configuration_group_description, sort_order, visible)
                VALUES ('" . self::CONFIGURATION_GROUP_TITLE . "', '" . self::CONFIGURATION_GROUP_TITLE . "', 1, 1);
            ");
            $configurationGroupId = (int)$this->dbConn->Insert_ID();
            $this->executeInstallerSql("
                UPDATE " . TABLE_CONFIGURATION_GROUP . "
                SET sort_order = $configurationGroupId
                WHERE configuration_group_id = $configurationGroupId
            ");
        } else {
            $configurationGroupId = (int)$sql->fields['configuration_group_id'];
        }

        // Register admin page
        zen_deregister_admin_pages(['configInstantSearch']);
        zen_register_admin_page('configInstantSearch', 'BOX_CONFIGURATION_INSTANT_SEARCH_OPTIONS', 'FILENAME_CONFIGURATION', "gID=$configurationGroupId", 'configuration', 'Y');

        $this->createConfigurationSettings($configurationGroupId);

        return true;
    }

    /**
     * Uninstall the plugin.
     *
     * @return bool
     */
    public function doUninstall(): bool
    {
        // Deregister admin pae
        zen_deregister_admin_pages(['configInstantSearch']);

        // Remove configuration settings
        $sql = "DELETE FROM " . TABLE_CONFIGURATION . "
                WHERE configuration_key LIKE 'INSTANT_SEARCH_%'";
        $this->executeInstallerSql($sql);

        // Remove configuration group
        $sql = "DELETE FROM " . TABLE_CONFIGURATION_GROUP . "
                WHERE configuration_group_title = '" . self::CONFIGURATION_GROUP_TITLE . "'";
        $this->executeInstallerSql($sql);

        // Remove FULLTEXT indexes from products_description table
        $index = $this->dbConn->Execute("
            SHOW INDEX
            FROM " . TABLE_PRODUCTS_DESCRIPTION . "
            WHERE key_name = 'idx_products_name'
            AND column_name = 'products_name'
            AND index_type = 'FULLTEXT'
        ");
        if ($index->RecordCount() > 0) {
            $this->executeInstallerSql("
                DROP INDEX idx_products_name
                ON " . TABLE_PRODUCTS_DESCRIPTION
            );
        }

        $index = $this->dbConn->Execute("
            SHOW INDEX
            FROM " . TABLE_PRODUCTS_DESCRIPTION . "
            WHERE key_name = 'idx_products_description'
            AND column_name = 'products_description'
            AND index_type = 'FULLTEXT'
        ");
        if ($index->RecordCount() > 0) {
            $this->executeInstallerSql("
                DROP INDEX idx_products_description
                ON " . TABLE_PRODUCTS_DESCRIPTION
            );
        }

        $this->executeInstallerSql("ALTER TABLE " . TABLE_PRODUCTS_DESCRIPTION . " ENGINE = MyISAM");

        return true;
    }

    /**
     * Add FULLTEXT indexes on products_description table, if not already present
     *
     * @return void
     */
    protected function createIndexes(): void
    {
        $this->executeInstallerSql("ALTER TABLE " . TABLE_PRODUCTS_DESCRIPTION . " ENGINE = InnoDB");

        $index = $this->dbConn->Execute("
            SHOW INDEX
            FROM " . TABLE_PRODUCTS_DESCRIPTION . "
            WHERE column_name = 'products_name'
            AND index_type = 'FULLTEXT'
        ");
        if ($index->RecordCount() < 0) {
            $this->executeInstallerSql("
                CREATE FULLTEXT INDEX idx_products_name
                ON " . TABLE_PRODUCTS_DESCRIPTION . "(products_name)
            ");
        }

        $index = $this->dbConn->Execute("
            SHOW INDEX
            FROM " . TABLE_PRODUCTS_DESCRIPTION . "
            WHERE column_name = 'products_description'
            AND index_type = 'FULLTEXT'
        ");
        if ($index->RecordCount() < 0) {
            $this->executeInstallerSql("
                CREATE FULLTEXT INDEX idx_products_description
                ON " . TABLE_PRODUCTS_DESCRIPTION . "(products_description)
            ");
        }

        $this->executeInstallerSql("OPTIMIZE TABLE " . TABLE_PRODUCTS_DESCRIPTION);
    }


    /**
     * Install admin settings.
     *
     * @param int $configurationGroupId
     * @return void
     */
    protected function createConfigurationSettings(int $configurationGroupId): void
    {
        // Remove any previous configuration settings
        $sql = "DELETE FROM " . TABLE_CONFIGURATION . "
                WHERE configuration_key LIKE 'INSTANT_SEARCH_%'";
        $this->executeInstallerSql($sql);

        // Insert configuration settings with default values
        $sql = "
            INSERT INTO " . TABLE_CONFIGURATION . "
                (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, date_added, sort_order, use_function, set_function, val_function)
            VALUES
                ('Dropdown - Enable','INSTANT_SEARCH_DROPDOWN_ENABLED', 'true', 'Enable dropdown suggestions on search input boxes.', $configurationGroupId, now(), 0, NULL, 'zen_cfg_select_option(array(\'true\', \'false\'),', NULL),
                ('Listing Page - Enable', 'INSTANT_SEARCH_PAGE_ENABLED', 'true', 'When the user submits a search form (advanced search form excluded), display the search results in a product listing page with infinite scroll.<br>It does not replace the (formerly advanced) search and results page.', $configurationGroupId, now(), 50, NULL, 'zen_cfg_select_option(array(\'true\', \'false\'),', NULL),
                ('Dropdown - Search Delay', 'INSTANT_SEARCH_DROPDOWN_INPUT_WAIT_TIME', '50', 'Delay the execution of the Instant Search query by this time period (in milliseconds), after a character is entered, to prevent unnecessary queries while the user is typing.', $configurationGroupId, now(), 100, NULL, NULL, '{\"error\":\"TEXT_INSTANT_SEARCH_CONFIGURATION_INT_VALIDATE\",\"id\":\"FILTER_VALIDATE_INT\",\"options\":{\"options\":{\"min_range\":0}}}'),
                ('Dropdown - Maximum Number of Results', 'INSTANT_SEARCH_DROPDOWN_MAX_RESULTS', '5', 'Maximum number of results displayed in the dropdown.', $configurationGroupId, now(), 150, NULL, NULL, '{\"error\":\"TEXT_INSTANT_SEARCH_CONFIGURATION_INT_VALIDATE\",\"id\":\"FILTER_VALIDATE_INT\",\"options\":{\"options\":{\"min_range\":0}}}'),
                ('Dropdown - Minimum Length of Search Query', 'INSTANT_SEARCH_DROPDOWN_MIN_WORDSEARCH_LENGTH', '3', 'Minimum number of characters that must be entered before Instant Search is initiated.', $configurationGroupId, now(), 200, NULL, NULL, '{\"error\":\"TEXT_INSTANT_SEARCH_CONFIGURATION_INT_VALIDATE\",\"id\":\"FILTER_VALIDATE_INT\",\"options\":{\"options\":{\"min_range\":0}}}'),
                ('Dropdown - Maximum Length of Search Query', 'INSTANT_SEARCH_DROPDOWN_MAX_WORDSEARCH_LENGTH', '100', 'Maximum string length allowed for Instant Search. If the search string length exceeds this value, the Instant Search will not be performed.', $configurationGroupId, now(), 250, NULL, NULL, '{\"error\":\"TEXT_INSTANT_SEARCH_CONFIGURATION_INT_VALIDATE\",\"id\":\"FILTER_VALIDATE_INT\",\"options\":{\"options\":{\"min_range\":0}}}'),
                ('Dropdown - Fields to Search and Order', 'INSTANT_SEARCH_DROPDOWN_FIELDS_LIST', 'model-exact,name-description,model-broad,category,manufacturer', 'List of the fields to search, separated by comma. You can exclude a field from the search by not including in the list.<br>The list also determines the order in which fields are searched, and therefore the position in the results. E.g. putting <code>model-exact</code> before <code>name-description</code> will show results where the model is equal to the query first, and then products that have name or description that contains the query.<br><br>List of fields:<ul><li><b>name-description</b> (product name and description) OR <b>name</b> (product name only, don\\'t search in descriptions) – only ONE of the two</li><li><b>model-exact</b> (product model - exact match, the query contains only and exactly the model)</li><li><b>model-broad</b> (product model - broad match, the query contains also the model of part of it)</li><li><b>category</b></li><li><b>manufacturer</b></li></ul>Default: <code>model-exact,name-description,model-broad,category,manufacturer</code><br>', $configurationGroupId, now(), 300, NULL, NULL, '{\"error\":\"TEXT_INSTANT_SEARCH_CONFIGURATION_FIELDS_LIST_VALIDATE\",\"id\":\"FILTER_CALLBACK\",\"options\":{\"options\":[\"\\\\\\\\Zencart\\\\\\\\Plugins\\\\\\\\Admin\\\\\\\\InstantSearch\\\\\\\\InstantSearchConfigurationValidation\",\"validateFieldsListDropdown\"]}}'),
                ('Dropdown - Include Related Products in the Results (Query Expansion)', 'INSTANT_SEARCH_DROPDOWN_USE_QUERY_EXPANSION', 'true', 'Show also products with related name and/or description (Query Expansion function of MySQL Full-Text Search).<br><br>Example: one product has name <em>Logitech Wired Keyboard</em> and another product has name <em>Microsoft Keyboard and Mouse, wireless set</em>. The user searches for <em>logitech</em>. Without query expansion, only the first product is shown. With query expansion, the second product is shown too, because it contains the word <em>keyboard</em> that is present also in the matched product (so it could be relevant for the user).<br><br>Note: this option is not equivalent to a typo-tolerance or synonym support feature (e.g. <em>Did you mean...?</em>).<br>', $configurationGroupId, now(), 350, NULL, 'zen_cfg_select_option(array(\'true\', \'false\'),', NULL),
                ('Dropdown - Display Images', 'INSTANT_SEARCH_DROPDOWN_DISPLAY_IMAGE', 'true', 'Display the product/category/manufacturer\'s image.', $configurationGroupId, now(), 400, NULL, 'zen_cfg_select_option(array(\'true\', \'false\'),', NULL),
                ('Dropdown - Display Product Price', 'INSTANT_SEARCH_DROPDOWN_DISPLAY_PRODUCT_PRICE', 'true', 'Display the product\'s price.', $configurationGroupId, now(), 450, NULL, 'zen_cfg_select_option(array(\'true\', \'false\'),', NULL),
                ('Dropdown - Display Product Model', 'INSTANT_SEARCH_DROPDOWN_DISPLAY_PRODUCT_MODEL', 'false', 'Display the product\'s model.', $configurationGroupId, now(), 500, NULL, 'zen_cfg_select_option(array(\'true\', \'false\'),', NULL),
                ('Dropdown - Display Category Count', 'INSTANT_SEARCH_DROPDOWN_DISPLAY_CATEGORIES_COUNT', 'true', 'Display the number of products for the matched categories.', $configurationGroupId, now(), 550, NULL, 'zen_cfg_select_option(array(\'true\', \'false\'),', NULL),
                ('Dropdown - Display Manufacturer Count', 'INSTANT_SEARCH_DROPDOWN_DISPLAY_MANUFACTURERS_COUNT', 'true', 'Display the number of products for the matched manufacturers.', $configurationGroupId, now(), 600, NULL, 'zen_cfg_select_option(array(\'true\', \'false\'),', NULL),
                ('Dropdown - Image Width', 'INSTANT_SEARCH_DROPDOWN_IMAGE_WIDTH', '100', 'Width of the product/category/manufacturer\'s image.', $configurationGroupId, now(), 650, NULL, NULL, '{\"error\":\"TEXT_INSTANT_SEARCH_CONFIGURATION_INT_VALIDATE\",\"id\":\"FILTER_VALIDATE_INT\",\"options\":{\"options\":{\"min_range\":0}}}'),
                ('Dropdown - Image Height', 'INSTANT_SEARCH_DROPDOWN_IMAGE_HEIGHT', '80', 'Height of the product/category/manufacturer\'s image.', $configurationGroupId, now(), 700, NULL, NULL, '{\"error\":\"TEXT_INSTANT_SEARCH_CONFIGURATION_INT_VALIDATE\",\"id\":\"FILTER_VALIDATE_INT\",\"options\":{\"options\":{\"min_range\":0}}}'),
                ('Dropdown - Higlight Search Terms in Bold', 'INSTANT_SEARCH_DROPDOWN_HIGHLIGHT_TEXT', 'suggestion', '<code>none</code>: no highlight<br><code>query</code>: highlight the user search terms<br><code>suggestion</code>: highlight the autocompleted text', $configurationGroupId, now(), 750, NULL, 'zen_cfg_select_option(array(\'none\', \'query\', \'suggestion\'),', NULL),
                ('Dropdown - Input Box Selector', 'INSTANT_SEARCH_DROPDOWN_INPUT_BOX_SELECTOR', 'input[name=\"keyword\"]', 'CSS selector of the search input box(es). You might need to change it if you\'re using a custom template and the results dropdown is not showing. Default: <code>input[name=\"keyword\"]</code>', $configurationGroupId, now(), 800, NULL, NULL, '{\"error\":\"ERROR\",\"id\":\"FILTER_SANITIZE_URL\",\"options\":{\"options\":{}}}'),
                ('Dropdown - Add Entry to Search Log', 'INSTANT_SEARCH_DROPDOWN_ADD_LOG_ENTRY', 'false', 'Add the searched terms to the Search Log report (if <em>Search Log</em> plugin is installed).', $configurationGroupId, now(), 850, NULL, 'zen_cfg_select_option(array(\'true\', \'false\'),', NULL),
                ('Listing Page - Number of Results per Page', 'INSTANT_SEARCH_PAGE_RESULTS_PER_PAGE', '10', 'Number of products per page displayed in the search results listing page.', $configurationGroupId, now(), 900, NULL, NULL, '{\"error\":\"TEXT_INSTANT_SEARCH_CONFIGURATION_INT_VALIDATE\",\"id\":\"FILTER_VALIDATE_INT\",\"options\":{\"options\":{\"min_range\":0}}}'),
                ('Listing Page - Fields to Search and Order', 'INSTANT_SEARCH_PAGE_FIELDS_LIST', 'model-exact,name-description,model-broad', 'List of the fields to search, separated by comma. You can exclude a field from the search by not including in the list.<br>The list also determines the order in which fields are searched, and therefore the position in the results. E.g. putting <code>model-exact</code> before <code>name-description</code> will show results where the model is equal to the query first, and then products that have name or description that contains the query.<br><br>List of fields:<ul><li><b>name-description</b> (product name and description) OR <b>name</b> (product name only, don\\'t search in descriptions) – only ONE of the two</li><li><b>model-exact</b> (product model - exact match, the query contains only and exactly the model)</li><li><b>model-broad</b> (product model - broad match, the query contains also the model of part of it)</li></ul>Default: <code>model-exact,name-description,model-broad</code><br>', $configurationGroupId, now(), 950, NULL, NULL, '{\"error\":\"TEXT_INSTANT_SEARCH_CONFIGURATION_FIELDS_LIST_VALIDATE\",\"id\":\"FILTER_CALLBACK\",\"options\":{\"options\":[\"\\\\\\\\Zencart\\\\\\\\Plugins\\\\\\\\Admin\\\\\\\\InstantSearch\\\\\\\\InstantSearchConfigurationValidation\",\"validateFieldsListPage\"]}}'),
                ('Listing Page - Include Related Products in the Results (Query Expansion)', 'INSTANT_SEARCH_PAGE_USE_QUERY_EXPANSION', 'true', 'Show also products with related name and/or description (Query Expansion function of MySQL Full-Text Search).<br><br>Example: one product has name <em>Logitech Wired Keyboard</em> and another product has name <em>Microsoft Keyboard and Mouse, wireless set</em>. The user searches for <em>logitech</em>. Without query expansion, only the first product is shown. With query expansion, the second product is shown too, because it contains the word <em>keyboard</em> that is present also in the matched product (so it could be relevant for the user).<br><br>Note: this option is not equivalent to a typo-tolerance or synonym support feature (e.g. <em>Did you mean...?</em>).<br>', $configurationGroupId, now(), 1000, NULL, 'zen_cfg_select_option(array(\'true\', \'false\'),', NULL),
                ('Listing Page - Add Entry to Search Log', 'INSTANT_SEARCH_PAGE_ADD_LOG_ENTRY', 'true', 'Add the searched terms to the Search Log report (if <em>Search Log</em> plugin is installed).', $configurationGroupId, now(), 1050, NULL, 'zen_cfg_select_option(array(\'true\', \'false\'),', NULL)
        ";
        $this->executeInstallerSql($sql);
    }

    /**
     * Restore admin settings values from the previous installed plugin version.
     *
     * @param int $configurationGroupId
     * @param string $oldPluginVersion
     * @return void
     */
    public function restorePreviousConfigurationValues(int $configurationGroupId, string $oldPluginVersion = ''): void
    {
        if (strpos($oldPluginVersion, 'v2') === 0) {

            // v2 settings have different names than v3's
            $v2settingNames = [
                'INSTANT_SEARCH_DROPDOWN_MAX_RESULTS'                 => 'INSTANT_SEARCH_MAX_NUMBER_OF_RESULTS',
                'INSTANT_SEARCH_DROPDOWN_MIN_WORDSEARCH_LENGTH'       => 'INSTANT_SEARCH_MIN_WORDSEARCH_LENGTH',
                'INSTANT_SEARCH_DROPDOWN_MAX_WORDSEARCH_LENGTH'       => 'INSTANT_SEARCH_MAX_WORDSEARCH_LENGTH',
                'INSTANT_SEARCH_DROPDOWN_DISPLAY_IMAGE'               => 'INSTANT_SEARCH_DISPLAY_IMAGE',
                'INSTANT_SEARCH_DROPDOWN_DISPLAY_PRODUCT_PRICE'       => 'INSTANT_SEARCH_DISPLAY_PRODUCT_PRICE',
                'INSTANT_SEARCH_DROPDOWN_DISPLAY_PRODUCT_MODEL'       => 'INSTANT_SEARCH_DISPLAY_PRODUCT_MODEL',
                'INSTANT_SEARCH_DROPDOWN_DISPLAY_CATEGORIES_COUNT'    => 'INSTANT_SEARCH_DISPLAY_CATEGORIES_COUNT',
                'INSTANT_SEARCH_DROPDOWN_DISPLAY_MANUFACTURERS_COUNT' => 'INSTANT_SEARCH_DISPLAY_MANUFACTURERS_COUNT',
            ];

            foreach ($v2settingNames as $k => $v2settingName) {
                if (defined($v2settingName)) {
                    $sql = "
                        UPDATE " . TABLE_CONFIGURATION . "
                        SET configuration_value = :value
                        WHERE configuration_key = :key
                    ";
                    $sql = $this->dbConn->bindVars($sql, ':value', constant($v2settingName), 'string');
                    $sql = $this->dbConn->bindVars($sql, ':key', $k, 'string');
                    $this->executeInstallerSql($sql);
                }
            }

            // Keep the dropdown wait time setting if it was changed from the v2 default, otherwise leave the
            // "new" v3 value
            if (defined('INSTANT_SEARCH_INPUT_WAIT_TIME') && INSTANT_SEARCH_INPUT_WAIT_TIME !== '150') {
                $sql = "
                    UPDATE " . TABLE_CONFIGURATION . "
                    SET configuration_value = :value
                    WHERE configuration_key = 'INSTANT_SEARCH_DROPDOWN_INPUT_WAIT_TIME'
                ";
                $sql = $this->dbConn->bindVars($sql, ':value', INSTANT_SEARCH_INPUT_WAIT_TIME, 'string');
                $this->executeInstallerSql($sql);
            }

        } else {

            $confSettings = $this->dbConn->Execute("
                SELECT configuration_key
                FROM " . TABLE_CONFIGURATION . "
                WHERE configuration_group_id = $configurationGroupId
            ");
            foreach ($confSettings as $confSetting) {
                if (defined($confSetting['configuration_key'])) {
                    $sql = "
                        UPDATE " . TABLE_CONFIGURATION . "
                        SET configuration_value = :value
                        WHERE configuration_key = :key
                    ";
                    $sql = $this->dbConn->bindVars($sql, ':value', constant($confSetting['configuration_key']), 'string');
                    $sql = $this->dbConn->bindVars($sql, ':key', $confSetting['configuration_key'], 'string');
                    $this->executeInstallerSql($sql);
                }
            }
        }
    }
}
