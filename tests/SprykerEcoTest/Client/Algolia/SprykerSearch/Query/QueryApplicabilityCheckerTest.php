<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEcoTest\Client\Algolia\SprykerSearch\Query;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\EntityToIndexMappingTransfer;
use Generated\Shared\Transfer\SearchContextTransfer;
use SprykerEco\Client\Algolia\AlgoliaConfig;
use SprykerEco\Client\Algolia\SprykerSearch\Query\QueryApplicabilityChecker;
use SprykerEcoTest\Client\Algolia\AlgoliaClientTester;

/**
 * Auto-generated group annotations
 *
 * @group SprykerEcoTest
 * @group Client
 * @group Algolia
 * @group SprykerSearch
 * @group Query
 * @group QueryApplicabilityCheckerTest
 * Add your own group annotations below this line
 */
class QueryApplicabilityCheckerTest extends Unit
{
    /**
     * @var \SprykerEcoTest\Client\Algolia\AlgoliaClientTester
     */
    protected AlgoliaClientTester $tester;

    public function testIsQueryApplicableReturnsFalseWhenAlgoliaIsNotActive(): void
    {
        // Arrange
        $this->tester->mockConfigMethod('getIsActive', false);
        /** @var \SprykerEco\Client\Algolia\AlgoliaConfig $algoliaConfig */
        $algoliaConfig = $this->tester->getModuleConfig();

        $searchContextTransfer = (new SearchContextTransfer())->setSourceIdentifier('product');
        $queryApplicabilityChecker = new QueryApplicabilityChecker($algoliaConfig);

        // Act
        $result = $queryApplicabilityChecker->isQueryApplicable($searchContextTransfer);

        // Assert
        $this->assertFalse($result);
    }

    public function testIsQueryApplicableReturnsTrueForWildcardSourceIdentifier(): void
    {
        // Arrange
        $this->tester->mockConfigMethod('getIsActive', true);
        /** @var \SprykerEco\Client\Algolia\AlgoliaConfig $algoliaConfig */
        $algoliaConfig = $this->tester->getModuleConfig();

        $searchContextTransfer = (new SearchContextTransfer())->setSourceIdentifier('*');
        $queryApplicabilityChecker = new QueryApplicabilityChecker($algoliaConfig);

        // Act
        $result = $queryApplicabilityChecker->isQueryApplicable($searchContextTransfer);

        // Assert
        $this->assertTrue($result);
    }

    public function testIsQueryApplicableReturnsTrueForProductWhenSearchInFrontendEnabledForProducts(): void
    {
        // Arrange
        $this->tester->mockConfigMethod('getIsActive', true);
        $this->tester->mockConfigMethod('isSearchInFrontendEnabledForProducts', true);
        /** @var \SprykerEco\Client\Algolia\AlgoliaConfig $algoliaConfig */
        $algoliaConfig = $this->tester->getModuleConfig();

        $searchContextTransfer = (new SearchContextTransfer())->setSourceIdentifier(AlgoliaConfig::SOURCE_IDENTIFIER_PRODUCT);
        $queryApplicabilityChecker = new QueryApplicabilityChecker($algoliaConfig);

        // Act
        $result = $queryApplicabilityChecker->isQueryApplicable($searchContextTransfer);

        // Assert
        $this->assertTrue($result);
    }

    public function testIsQueryApplicableReturnsFalseForProductWhenSearchInFrontendNotEnabledForProducts(): void
    {
        // Arrange
        $this->tester->mockConfigMethod('getIsActive', true);
        $this->tester->mockConfigMethod('isSearchInFrontendEnabledForProducts', false);
        $this->tester->mockConfigMethod('getEntityToIndexMappings', []);
        /** @var \SprykerEco\Client\Algolia\AlgoliaConfig $algoliaConfig */
        $algoliaConfig = $this->tester->getModuleConfig();

        $searchContextTransfer = (new SearchContextTransfer())->setSourceIdentifier(AlgoliaConfig::SOURCE_IDENTIFIER_PRODUCT);
        $queryApplicabilityChecker = new QueryApplicabilityChecker($algoliaConfig);

        // Act
        $result = $queryApplicabilityChecker->isQueryApplicable($searchContextTransfer);

        // Assert
        $this->assertFalse($result);
    }

    public function testIsQueryApplicableReturnsTrueForCmsPageWhenSearchInFrontendEnabledForCmsPages(): void
    {
        // Arrange
        $this->tester->mockConfigMethod('getIsActive', true);
        $this->tester->mockConfigMethod('isSearchInFrontendEnabledForCmsPages', true);
        /** @var \SprykerEco\Client\Algolia\AlgoliaConfig $algoliaConfig */
        $algoliaConfig = $this->tester->getModuleConfig();

        $searchContextTransfer = (new SearchContextTransfer())->setSourceIdentifier(AlgoliaConfig::SOURCE_IDENTIFIER_CMS_PAGE);
        $queryApplicabilityChecker = new QueryApplicabilityChecker($algoliaConfig);

        // Act
        $result = $queryApplicabilityChecker->isQueryApplicable($searchContextTransfer);

        // Assert
        $this->assertTrue($result);
    }

    public function testIsQueryApplicableReturnsFalseForCmsPageWhenSearchInFrontendNotEnabledForCmsPages(): void
    {
        // Arrange
        $this->tester->mockConfigMethod('getIsActive', true);
        $this->tester->mockConfigMethod('isSearchInFrontendEnabledForCmsPages', false);
        $this->tester->mockConfigMethod('getEntityToIndexMappings', []);
        /** @var \SprykerEco\Client\Algolia\AlgoliaConfig $algoliaConfig */
        $algoliaConfig = $this->tester->getModuleConfig();

        $searchContextTransfer = (new SearchContextTransfer())->setSourceIdentifier(AlgoliaConfig::SOURCE_IDENTIFIER_CMS_PAGE);
        $queryApplicabilityChecker = new QueryApplicabilityChecker($algoliaConfig);

        // Act
        $result = $queryApplicabilityChecker->isQueryApplicable($searchContextTransfer);

        // Assert
        $this->assertFalse($result);
    }

    public function testIsQueryApplicableReturnsTrueWhenSourceIdentifierFoundInEntityToIndexMappings(): void
    {
        // Arrange
        $this->tester->mockConfigMethod('getIsActive', true);
        $this->tester->mockConfigMethod('isSearchInFrontendEnabledForProducts', false);
        $this->tester->mockConfigMethod('isSearchInFrontendEnabledForCmsPages', false);
        $this->tester->mockConfigMethod('getEntityToIndexMappings', [
            [
                EntityToIndexMappingTransfer::SOURCE_IDENTIFIER => 'document',
                'store' => '*',
                'locales' => ['en_US', 'de_DE'],
                'indexName' => 'stan-dev-cms-page-en_us',
            ],
        ]);
        /** @var \SprykerEco\Client\Algolia\AlgoliaConfig $algoliaConfig */
        $algoliaConfig = $this->tester->getModuleConfig();

        $searchContextTransfer = (new SearchContextTransfer())->setSourceIdentifier('document');
        $queryApplicabilityChecker = new QueryApplicabilityChecker($algoliaConfig);

        // Act
        $result = $queryApplicabilityChecker->isQueryApplicable($searchContextTransfer);

        // Assert
        $this->assertTrue($result);
    }

    public function testIsQueryApplicableReturnsFalseWhenSourceIdentifierNotFoundInEntityToIndexMappings(): void
    {
        // Arrange
        $this->tester->mockConfigMethod('getIsActive', true);
        $this->tester->mockConfigMethod('isSearchInFrontendEnabledForProducts', false);
        $this->tester->mockConfigMethod('isSearchInFrontendEnabledForCmsPages', false);
        $this->tester->mockConfigMethod('getEntityToIndexMappings', [
            [
                EntityToIndexMappingTransfer::SOURCE_IDENTIFIER => 'document',
                'store' => '*',
                'locales' => ['en_US', 'de_DE'],
                'indexName' => 'stan-dev-cms-page-en_us',
            ],
        ]);
        /** @var \SprykerEco\Client\Algolia\AlgoliaConfig $algoliaConfig */
        $algoliaConfig = $this->tester->getModuleConfig();

        $searchContextTransfer = (new SearchContextTransfer())->setSourceIdentifier('unknown-entity');
        $queryApplicabilityChecker = new QueryApplicabilityChecker($algoliaConfig);

        // Act
        $result = $queryApplicabilityChecker->isQueryApplicable($searchContextTransfer);

        // Assert
        $this->assertFalse($result);
    }

    public function testIsQueryApplicableReturnsTrueWhenMultipleMappingsExistAndOneMatches(): void
    {
        // Arrange
        $this->tester->mockConfigMethod('getIsActive', true);
        $this->tester->mockConfigMethod('isSearchInFrontendEnabledForProducts', false);
        $this->tester->mockConfigMethod('isSearchInFrontendEnabledForCmsPages', false);
        $this->tester->mockConfigMethod('getEntityToIndexMappings', [
            [
                EntityToIndexMappingTransfer::SOURCE_IDENTIFIER => 'document',
                'store' => '*',
                'locales' => ['en_US', 'de_DE'],
                'indexName' => 'stan-dev-cms-page-en_us',
            ],
            [
                EntityToIndexMappingTransfer::SOURCE_IDENTIFIER => 'category',
                'store' => 'DE',
                'locales' => ['de_DE'],
                'indexName' => 'stan-dev-category-de_de',
            ],
        ]);
        /** @var \SprykerEco\Client\Algolia\AlgoliaConfig $algoliaConfig */
        $algoliaConfig = $this->tester->getModuleConfig();

        $searchContextTransfer = (new SearchContextTransfer())->setSourceIdentifier('category');
        $queryApplicabilityChecker = new QueryApplicabilityChecker($algoliaConfig);

        // Act
        $result = $queryApplicabilityChecker->isQueryApplicable($searchContextTransfer);

        // Assert
        $this->assertTrue($result);
    }

    public function testIsQueryApplicableReturnsFalseWhenEntityToIndexMappingsAreEmptyAndOtherConditionsNotMet(): void
    {
        // Arrange
        $this->tester->mockConfigMethod('getIsActive', true);
        $this->tester->mockConfigMethod('isSearchInFrontendEnabledForProducts', false);
        $this->tester->mockConfigMethod('isSearchInFrontendEnabledForCmsPages', false);
        $this->tester->mockConfigMethod('getEntityToIndexMappings', []);
        /** @var \SprykerEco\Client\Algolia\AlgoliaConfig $algoliaConfig */
        $algoliaConfig = $this->tester->getModuleConfig();

        $searchContextTransfer = (new SearchContextTransfer())->setSourceIdentifier('some-entity');
        $queryApplicabilityChecker = new QueryApplicabilityChecker($algoliaConfig);

        // Act
        $result = $queryApplicabilityChecker->isQueryApplicable($searchContextTransfer);

        // Assert
        $this->assertFalse($result);
    }
}
