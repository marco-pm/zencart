<?php
declare(strict_types=1);

namespace Tests\InstantSearch\Unit;

use Composer\Autoload\ClassLoader;
use Tests\Support\zcUnitTestCase;

abstract class InstantSearchUnitTest extends zcUnitTestCase
{
    public function __construct(
        ?string $name = null,
        array $data = [],
        $dataName = '',
        public string $instantSearchClassName = ''
    ) {
        parent::__construct($name, $data, $dataName);
    }

    public function setUp(): void
    {
        parent::setUp();

        $classLoader = new ClassLoader();
        $classLoader->addPsr4("Zencart\\Plugins\\Catalog\\InstantSearch\\", "zc_plugins/InstantSearch/v3.0.0/classes/", true);
        $classLoader->register();

        define('PRODUCT_LIST_MODEL', '0');
        define('PRODUCT_LIST_NAME', '1');
        define('PRODUCT_LIST_MANUFACTURER', '2');
        define('PRODUCT_LIST_PRICE', '3');
        define('PRODUCT_LIST_QUANTITY', '4');
        define('PRODUCT_LIST_WEIGHT', '5');
        define('PRODUCT_LIST_IMAGE', '6');
        define('TEXT_INSTANT_SEARCH_CONFIGURATION_ERROR', 'Configuration error');

        define('INSTANT_SEARCH_DROPDOWN_MIN_WORDSEARCH_LENGTH', '3');
        define('INSTANT_SEARCH_DROPDOWN_MAX_WORDSEARCH_LENGTH', '30');
        define('INSTANT_SEARCH_DROPDOWN_MAX_RESULTS', '5');
        define('INSTANT_SEARCH_DROPDOWN_USE_QUERY_EXPANSION', 'true');
        define('INSTANT_SEARCH_DROPDOWN_ADD_LOG_ENTRY', 'true');
        define('TEXT_SEARCH_LOG_ENTRY_DROPDOWN_PREFIX', '');
    }

    // Note: test methods use the PHPUnit Annotations `@runInSeparateProcess` and `@preserveGlobalState disabled` in order
    // to run each test in a separate process, thus making it possible to override constants in the same test class.

    abstract public function keywordProvider(): array;

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     * @dataProvider keywordProvider
     */
    public function testKeywordReturnsEmpty(string $keyword, string $expectedOutput): void
    {
        $instantSearchMock = $this->getMockBuilder($this->instantSearchClassName)
                                  ->onlyMethods(['searchDb', 'formatResults', 'addEntryToSearchLog'])
                                  ->getMock();

        $instantSearchMock->expects($this->never())
                          ->method('searchDb');

        $_POST['keyword'] = $keyword;
        $htmlOutput = $instantSearchMock->instantSearch();
        $this->assertEquals($expectedOutput, $htmlOutput);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testInvalidFieldNameSettingReturnsError(): void
    {
        define('INSTANT_SEARCH_DROPDOWN_FIELDS_LIST', 'gibberish,name-description,model-broad');
        define('INSTANT_SEARCH_PAGE_FIELDS_LIST', 'gibberish,name-description,model-broad');

        $instantSearchMock = $this->getMockBuilder($this->instantSearchClassName)
                                  ->onlyMethods(['execQuery', 'formatResults', 'addEntryToSearchLog'])
                                  ->getMock();

        $_POST['keyword'] = 'whatever';
        $htmlOutput = $instantSearchMock->instantSearch();
        $this->assertStringContainsString(TEXT_INSTANT_SEARCH_CONFIGURATION_ERROR, $htmlOutput);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testCommonFieldsValuesCallCorrespondingSql(bool $useQueryExpansion = true): void
    {
        define('INSTANT_SEARCH_DROPDOWN_FIELDS_LIST', 'name,model-exact,model-broad');
        define('INSTANT_SEARCH_PAGE_FIELDS_LIST', 'name,model-exact,model-broad');

        $instantSearchMock = $this->getMockBuilder($this->instantSearchClassName)
                                  ->onlyMethods([
                                      'execQuery', 'formatResults', 'addEntryToSearchLog',
                                      'buildSqlProductModel', 'buildSqlProductName', 'buildSqlProductNameDescriptionMatch'
                                  ])
                                  ->getMock();

        $_POST['keyword'] = 'whatever';

        $instantSearchMock->expects($this->exactly(2))
                          ->method('buildSqlProductModel')
                          ->withConsecutive([true], [false]);

        $instantSearchMock->expects($this->exactly(2))
                          ->method('buildSqlProductName')
                          ->withConsecutive([true], [false]);

        $instantSearchMock->expects($this->once())
                          ->method('buildSqlProductNameDescriptionMatch')
                          ->with(false, $useQueryExpansion);

        $instantSearchMock->instantSearch();
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testNameWithDescriptionFieldCallsCorrespondingSql(bool $useQueryExpansion = true): void
    {
        define('INSTANT_SEARCH_DROPDOWN_FIELDS_LIST', 'name-description');
        define('INSTANT_SEARCH_PAGE_FIELDS_LIST', 'name-description');

        $instantSearchMock = $this->getMockBuilder($this->instantSearchClassName)
                                  ->onlyMethods(['execQuery', 'formatResults', 'addEntryToSearchLog', 'buildSqlProductNameDescriptionMatch'])
                                  ->getMock();

        $_POST['keyword'] = 'whatever';

        $instantSearchMock->expects($this->once())
                          ->method('buildSqlProductNameDescriptionMatch')
                          ->with(true, $useQueryExpansion);

        $instantSearchMock->instantSearch();
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testSaveSearchLogIfEnabled(): void
    {
        define('INSTANT_SEARCH_DROPDOWN_FIELDS_LIST', 'name-description');
        define('INSTANT_SEARCH_PAGE_FIELDS_LIST', 'name-description');

        $instantSearchMock = $this->getMockBuilder($this->instantSearchClassName)
                                  ->onlyMethods(['searchDb', 'formatResults', 'addEntryToSearchLog'])
                                  ->getMock();

        $_POST['keyword'] = 'whatever';

        $instantSearchMock->expects($this->once())
                     ->method('addEntryToSearchLog');

        $instantSearchMock->instantSearch();
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testEmptyOutputAndZeroCountWhenNoResults(): void
    {
        define('INSTANT_SEARCH_DROPDOWN_FIELDS_LIST', 'name-description');

        $instantSearchMock = $this->getMockBuilder($this->instantSearchClassName)
                                  ->onlyMethods(['searchDb', 'formatResults', 'addEntryToSearchLog'])
                                  ->getMock();

        $_POST['keyword'] = 'whatever';

        $htmlOutput = $instantSearchMock->instantSearch();

        $this->assertEquals('{"count":0,"results":""}', $htmlOutput);
    }
}
