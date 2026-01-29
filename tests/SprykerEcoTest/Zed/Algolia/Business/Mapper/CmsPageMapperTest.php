<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEcoTest\Zed\Algolia\Business\Mapper;

use ArrayObject;
use Codeception\Test\Unit;
use Generated\Shared\Transfer\CmsPageAttributesTransfer;
use Generated\Shared\Transfer\CmsPageMetaAttributesTransfer;
use Generated\Shared\Transfer\CmsPagePublishedTransfer;
use Generated\Shared\Transfer\CmsPageTransfer;
use Generated\Shared\Transfer\StoreRelationTransfer;
use Generated\Shared\Transfer\StoreTransfer;
use Spryker\Shared\Kernel\Transfer\Exception\NullValueException;
use SprykerEco\Shared\Algolia\Enum\AlgoliaCmsPageObjectEnum;
use SprykerEco\Zed\Algolia\Business\Mapper\CmsPageMapper;

/**
 * Auto-generated group annotations
 *
 * @group PyzTest
 * @group Zed
 * @group Algolia
 * @group Business
 * @group Mapper
 * @group CmsPageMapperTest
 * Add your own group annotations below this line
 */
class CmsPageMapperTest extends Unit
{
    /**
     * @var string
     */
    protected const TEST_LOCALE = 'en_US';

    /**
     * @var string
     */
    protected const TEST_URL = '/test-page';

    /**
     * @var string
     */
    protected const TEST_TITLE = 'Test Page Title';

    /**
     * @var string
     */
    protected const TEST_CONTENT = 'Test Page Content';

    /**
     * @var string
     */
    protected const TEST_META_TITLE = 'Test Meta Title';

    /**
     * @var string
     */
    protected const TEST_META_DESCRIPTION = 'Test Meta Description';

    /**
     * @var string
     */
    protected const TEST_META_KEYWORDS = 'test, keywords';

    /**
     * @var int
     */
    protected const TEST_CMS_PAGE_ID = 123;

    /**
     * @var \SprykerEcoTest\Zed\Algolia\AlgoliaBusinessTester
     */
    protected $tester;

    /**
     * @return void
     */
    public function testMapCmsPageDataToAlgoliaDataReturnsCorrectStructure(): void
    {
        // Arrange
        $cmsPageMapper = new CmsPageMapper();

        $cmsPagePublishedTransfer = $this->createCmsPagePublishedTransfer();
        $cmsPageTransfer = $this->createCmsPageTransfer();
        $flattenedLocaleCmsPageData = $this->createFlattenedLocaleCmsPageData();

        // Act
        $result = $cmsPageMapper->mapCmsPageDataToAlgoliaData(
            $cmsPagePublishedTransfer,
            static::TEST_LOCALE,
            $cmsPageTransfer,
            $flattenedLocaleCmsPageData,
        );

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey(AlgoliaCmsPageObjectEnum::OBJECT_ID->value, $result);
        $this->assertArrayHasKey(AlgoliaCmsPageObjectEnum::STORE->value, $result);
        $this->assertArrayHasKey(AlgoliaCmsPageObjectEnum::LOCALE->value, $result);
        $this->assertArrayHasKey(AlgoliaCmsPageObjectEnum::IS_ACTIVE->value, $result);
        $this->assertArrayHasKey(AlgoliaCmsPageObjectEnum::URL->value, $result);
        $this->assertArrayHasKey(AlgoliaCmsPageObjectEnum::TITLE->value, $result);
        $this->assertArrayHasKey(AlgoliaCmsPageObjectEnum::CONTENT->value, $result);
        $this->assertArrayHasKey(AlgoliaCmsPageObjectEnum::META_TITLE->value, $result);
        $this->assertArrayHasKey(AlgoliaCmsPageObjectEnum::META_DESCRIPTION->value, $result);
        $this->assertArrayHasKey(AlgoliaCmsPageObjectEnum::META_KEYWORDS->value, $result);
        $this->assertArrayHasKey(AlgoliaCmsPageObjectEnum::LAST_UPDATED->value, $result);
        $this->assertArrayHasKey(AlgoliaCmsPageObjectEnum::CREATED->value, $result);

        $this->assertEquals(static::TEST_CMS_PAGE_ID, $result[AlgoliaCmsPageObjectEnum::OBJECT_ID->value]);
        $this->assertEquals(['DE'], $result[AlgoliaCmsPageObjectEnum::STORE->value]);
        $this->assertEquals(static::TEST_LOCALE, $result[AlgoliaCmsPageObjectEnum::LOCALE->value]);
        $this->assertTrue($result[AlgoliaCmsPageObjectEnum::IS_ACTIVE->value]);
        $this->assertEquals(static::TEST_URL, $result[AlgoliaCmsPageObjectEnum::URL->value]);
        $this->assertEquals(static::TEST_TITLE, $result[AlgoliaCmsPageObjectEnum::TITLE->value]);
        $this->assertEquals(static::TEST_CONTENT, $result[AlgoliaCmsPageObjectEnum::CONTENT->value]);
        $this->assertEquals(static::TEST_META_TITLE, $result[AlgoliaCmsPageObjectEnum::META_TITLE->value]);
        $this->assertEquals(static::TEST_META_DESCRIPTION, $result[AlgoliaCmsPageObjectEnum::META_DESCRIPTION->value]);
        $this->assertEquals(static::TEST_META_KEYWORDS, $result[AlgoliaCmsPageObjectEnum::META_KEYWORDS->value]);
    }

    /**
     * @return void
     */
    public function testMapCmsPageDataToAlgoliaDataStripsHtmlTags(): void
    {
        // Arrange
        $cmsPageMapper = new CmsPageMapper();

        $cmsPagePublishedTransfer = $this->createCmsPagePublishedTransfer();
        $cmsPageTransfer = $this->createCmsPageTransfer();

        $flattenedLocaleCmsPageData = [
            'placeholders' => [
                'title' => '<h1>Title with <strong>HTML</strong> tags</h1>',
                'content' => '<p>Content with <a href="#">links</a> and <em>emphasis</em></p>',
            ],
        ];

        // Act
        $result = $cmsPageMapper->mapCmsPageDataToAlgoliaData(
            $cmsPagePublishedTransfer,
            static::TEST_LOCALE,
            $cmsPageTransfer,
            $flattenedLocaleCmsPageData,
        );

        // Assert
        $this->assertEquals('Title with HTML tags', $result[AlgoliaCmsPageObjectEnum::TITLE->value]);
        $this->assertEquals('Content with links and emphasis', $result[AlgoliaCmsPageObjectEnum::CONTENT->value]);
    }

    /**
     * @return void
     */
    public function testMapCmsPageDataToAlgoliaDataTruncatesLongText(): void
    {
        // Arrange
        $cmsPageMapper = new CmsPageMapper();

        $cmsPagePublishedTransfer = $this->createCmsPagePublishedTransfer();
        $cmsPageTransfer = $this->createCmsPageTransfer();

        // Create text larger than 90KB
        $longText = str_repeat('A', 91 * 1024);

        $flattenedLocaleCmsPageData = [
            'placeholders' => [
                'title' => static::TEST_TITLE,
                'content' => $longText,
            ],
        ];

        // Act
        $result = $cmsPageMapper->mapCmsPageDataToAlgoliaData(
            $cmsPagePublishedTransfer,
            static::TEST_LOCALE,
            $cmsPageTransfer,
            $flattenedLocaleCmsPageData,
        );

        // Assert
        $this->assertStringContainsString('... [content truncated due to size limit]', $result[AlgoliaCmsPageObjectEnum::CONTENT->value]);
        $this->assertLessThan(91 * 1024, strlen($result[AlgoliaCmsPageObjectEnum::CONTENT->value]));
    }

    /**
     * @return void
     */
    public function testMapCmsPageDataToAlgoliaDataHandlesEmptyPlaceholders(): void
    {
        // Arrange
        $cmsPageMapper = new CmsPageMapper();

        $cmsPagePublishedTransfer = $this->createCmsPagePublishedTransfer();
        $cmsPageTransfer = $this->createCmsPageTransfer();

        $flattenedLocaleCmsPageData = [
            'placeholders' => [],
        ];

        // Act
        $result = $cmsPageMapper->mapCmsPageDataToAlgoliaData(
            $cmsPagePublishedTransfer,
            static::TEST_LOCALE,
            $cmsPageTransfer,
            $flattenedLocaleCmsPageData,
        );

        // Assert
        $this->assertEquals('', $result[AlgoliaCmsPageObjectEnum::TITLE->value]);
        $this->assertEquals('', $result[AlgoliaCmsPageObjectEnum::CONTENT->value]);
    }

    /**
     * @return void
     */
    public function testMapCmsPageDataToAlgoliaDataHandlesMissingLocaleAttributes(): void
    {
        // Arrange
        $cmsPageMapper = new CmsPageMapper();

        $cmsPagePublishedTransfer = $this->createCmsPagePublishedTransfer();

        // Create CMS page without matching locale attributes
        $cmsPageAttributesTransfer = (new CmsPageAttributesTransfer())
            ->setLocaleName('de_DE') // Different locale
            ->setUrl('/different-page');

        $cmsPageTransfer = (new CmsPageTransfer())
            ->setIsActive(true)
            ->setStoreRelation($this->createStoreRelation())
            ->addPageAttribute($cmsPageAttributesTransfer)
            ->setMetaAttributes(new ArrayObject());

        $flattenedLocaleCmsPageData = $this->createFlattenedLocaleCmsPageData();

        // Act & Assert - Should throw exception when url is not found for locale
        $this->expectException(NullValueException::class);
        $this->expectExceptionMessage('Property "url" of transfer `Generated\Shared\Transfer\CmsPageAttributesTransfer` is null.');

        $cmsPageMapper->mapCmsPageDataToAlgoliaData(
            $cmsPagePublishedTransfer,
            static::TEST_LOCALE,
            $cmsPageTransfer,
            $flattenedLocaleCmsPageData,
        );
    }

    /**
     * @return void
     */
    public function testMapCmsPageDataToAlgoliaDataHandlesMissingMetaAttributes(): void
    {
        // Arrange
        $cmsPageMapper = new CmsPageMapper();

        $cmsPagePublishedTransfer = $this->createCmsPagePublishedTransfer();

        $cmsPageTransfer = $this->createCmsPageTransfer();
        $cmsPageTransfer->setMetaAttributes(new ArrayObject()); // Empty meta attributes

        $flattenedLocaleCmsPageData = $this->createFlattenedLocaleCmsPageData();

        // Act
        $result = $cmsPageMapper->mapCmsPageDataToAlgoliaData(
            $cmsPagePublishedTransfer,
            static::TEST_LOCALE,
            $cmsPageTransfer,
            $flattenedLocaleCmsPageData,
        );

        // Assert
        $this->assertNull($result[AlgoliaCmsPageObjectEnum::META_TITLE->value]);
        $this->assertNull($result[AlgoliaCmsPageObjectEnum::META_DESCRIPTION->value]);
        $this->assertNull($result[AlgoliaCmsPageObjectEnum::META_KEYWORDS->value]);
    }

    /**
     * @return \Generated\Shared\Transfer\CmsPagePublishedTransfer
     */
    protected function createCmsPagePublishedTransfer(): CmsPagePublishedTransfer
    {
        return (new CmsPagePublishedTransfer())
            ->setId(static::TEST_CMS_PAGE_ID)
            ->setCreatedAt('2023-01-01 10:00:00')
            ->setUpdatedAt('2023-01-02 15:30:00');
    }

    /**
     * @return \Generated\Shared\Transfer\CmsPageTransfer
     */
    protected function createCmsPageTransfer(): CmsPageTransfer
    {
        $cmsPageAttributesTransfer = (new CmsPageAttributesTransfer())
            ->setLocaleName(static::TEST_LOCALE)
            ->setUrl(static::TEST_URL);

        $cmsPageMetaAttributesTransfer = (new CmsPageMetaAttributesTransfer())
            ->setLocaleName(static::TEST_LOCALE)
            ->setMetaTitle(static::TEST_META_TITLE)
            ->setMetaDescription(static::TEST_META_DESCRIPTION)
            ->setMetaKeywords(static::TEST_META_KEYWORDS);

        return (new CmsPageTransfer())
            ->setIsActive(true)
            ->setStoreRelation($this->createStoreRelation())
            ->addPageAttribute($cmsPageAttributesTransfer)
            ->addMetaAttribute($cmsPageMetaAttributesTransfer);
    }

    /**
     * @return \Generated\Shared\Transfer\StoreRelationTransfer
     */
    protected function createStoreRelation(): StoreRelationTransfer
    {
        $storeTransfer = (new StoreTransfer())->setName('DE');

        return (new StoreRelationTransfer())
            ->addStores($storeTransfer);
    }

    /**
     * @return array
     */
    protected function createFlattenedLocaleCmsPageData(): array
    {
        return [
            'placeholders' => [
                'title' => static::TEST_TITLE,
                'content' => static::TEST_CONTENT,
            ],
        ];
    }
}
