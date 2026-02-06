<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEcoTest\Client\Algolia\Api\SearchParameters;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\FacetParametersTransfer;
use Generated\Shared\Transfer\SearchRequestTransfer;
use SprykerEco\Client\Algolia\Exception\FacetTypeUnknownException;
use SprykerEcoTest\Client\Algolia\AlgoliaClientTester;

/**
 * Auto-generated group annotations
 *
 * @group SprykerEcoTest
 * @group Client
 * @group Algolia
 * @group Business
 * @group Api
 * @group SearchParameters
 * @group FilterConverterTest
 * Add your own group annotations below this line
 */
class FilterConverterTest extends Unit
{
    /**
     * @var string
     */
    protected const ID_TENANT = 'test_tenant';

    /**
     * @var \SprykerEcoTest\Client\Algolia\AlgoliaClientTester
     */
    protected AlgoliaClientTester $tester;

    protected function _before()
    {
        $this->tester->mockConfigMethod('getTenantIdentifier', static::ID_TENANT);
    }

    /**
     * @return void
     */
    public function testConvertFacetCollectionTransferToAlgoliaFiltersStringReturnsEmptyResultForEmptyCollection()
    {
        // Arrange
        $this->tester->haveCacheAdapterMock(static::ID_TENANT, ['test-facet', 'prices']);
        $filterConverter = $this->tester->getFactory()->createFilterConverter();
        $facetCollection = $this->tester->haveFacetCollectionTransfer([
            'facet1' => $this->tester->haveFacetEntryTransfer('wrongType', $this->tester->haveFacetParametersTransfer()),
        ]);
        $searchRequestTransfer = $this->tester->haveSearchRequestTransfer([SearchRequestTransfer::FACETS => $facetCollection]);

        // Act
        $filters = $filterConverter->convertFacetCollectionTransferToAlgoliaFiltersString($searchRequestTransfer);

        // Assert
        $this->assertEquals('price>=0', $filters);
    }

    /**
     * @return void
     */
    public function testConvertFacetCollectionTransferToAlgoliaFiltersStringThrowsExceptionForWrongFacetType()
    {
        // Arrange
        $this->tester->haveCacheAdapterMock(static::ID_TENANT, ['attributes.facet1', 'test-facet', 'prices']);
        $filterConverter = $this->tester->getFactory()->createFilterConverter();
        $facetCollection = $this->tester->haveFacetCollectionTransfer([
            'facet1' => $this->tester->haveFacetEntryTransfer('wrongType', $this->tester->haveFacetParametersTransfer()),
        ]);
        $searchRequestTransfer = $this->tester->haveSearchRequestTransfer([SearchRequestTransfer::FACETS => $facetCollection]);

        // Assert
        $this->expectException(FacetTypeUnknownException::class);

        // Act
        $filterConverter->convertFacetCollectionTransferToAlgoliaFiltersString($searchRequestTransfer);
    }

    /**
     * @return void
     */
    public function testConvertFacetCollectionTransferToAlgoliaFiltersStringReturnsCorrectResultForRangeFacet()
    {
        // Arrange
        $this->tester->haveCacheAdapterMock(static::ID_TENANT, ['attributes.facet1', 'test-facet', 'prices']);
        $filterConverter = $this->tester->getFactory()->createFilterConverter();
        $facetCollection = $this->tester->haveFacetCollectionTransfer([
            'facet1' => $this->tester->haveFacetEntryTransfer('range', $this->tester->haveFacetParametersTransfer([
                FacetParametersTransfer::FROM => 100,
                FacetParametersTransfer::TO => 250,
                FacetParametersTransfer::VALUES => null,
            ])),
        ]);
        $searchRequestTransfer = $this->tester->haveSearchRequestTransfer([SearchRequestTransfer::FACETS => $facetCollection]);

        // Act
        $filtersString = $filterConverter->convertFacetCollectionTransferToAlgoliaFiltersString($searchRequestTransfer);

        // Assert
        $this->assertEquals('price>=0 AND attributes.facet1:100.000000 TO 250.000000', $filtersString);
    }

    /**
     * @return void
     */
    public function testConvertFacetCollectionTransferToAlgoliaFiltersStringReturnsCorrectResultForRangeFacetNotFromAttributes()
    {
        // Arrange
        $this->tester->haveCacheAdapterMock(static::ID_TENANT, ['rating', 'test-facet', 'prices']);
        $filterConverter = $this->tester->getFactory()->createFilterConverter();
        $facetCollection = $this->tester->haveFacetCollectionTransfer([
            'rating' => $this->tester->haveFacetEntryTransfer('range', $this->tester->haveFacetParametersTransfer([
                FacetParametersTransfer::FROM => 100,
                FacetParametersTransfer::TO => 250,
                FacetParametersTransfer::VALUES => null,
            ])),
        ]);
        $searchRequestTransfer = $this->tester->haveSearchRequestTransfer([SearchRequestTransfer::FACETS => $facetCollection]);

        // Act
        $filtersString = $filterConverter->convertFacetCollectionTransferToAlgoliaFiltersString($searchRequestTransfer);

        // Assert
        $this->assertEquals('price>=0 AND rating:100.000000 TO 250.000000', $filtersString);
    }

    /**
     * @return void
     */
    public function testConvertFacetCollectionTransferToAlgoliaFiltersStringWithEmptyToAttributeReturnsCorrectResultForRangeFacetNotFromAttributes()
    {
        // Arrange
        $this->tester->haveCacheAdapterMock(static::ID_TENANT, ['rating', 'attributes.facet2', 'prices']);
        $filterConverter = $this->tester->getFactory()->createFilterConverter();
        $facetCollection = $this->tester->haveFacetCollectionTransfer([
            'rating' => $this->tester->haveFacetEntryTransfer('range', $this->tester->haveFacetParametersTransfer([
                FacetParametersTransfer::FROM => 100,
                FacetParametersTransfer::TO => null,
                FacetParametersTransfer::VALUES => null,
            ])),
        ]);
        $searchRequestTransfer = $this->tester->haveSearchRequestTransfer([SearchRequestTransfer::FACETS => $facetCollection]);

        // Act
        $filtersString = $filterConverter->convertFacetCollectionTransferToAlgoliaFiltersString($searchRequestTransfer);

        // Assert
        $this->assertEquals('price>=0 AND rating>=100.000000', $filtersString);
    }

    /**
     * @return void
     */
    public function testConvertFacetCollectionTransferToAlgoliaFiltersStringWithZeroToAttributeReturnsCorrectResultForRangeFacetNotFromAttributes()
    {
        // Arrange
        $this->tester->haveCacheAdapterMock(static::ID_TENANT, ['rating', 'attributes.facet2', 'prices']);
        $filterConverter = $this->tester->getFactory()->createFilterConverter();
        $facetCollection = $this->tester->haveFacetCollectionTransfer([
            'rating' => $this->tester->haveFacetEntryTransfer('range', $this->tester->haveFacetParametersTransfer([
                FacetParametersTransfer::FROM => 100,
                FacetParametersTransfer::TO => 0,
                FacetParametersTransfer::VALUES => null,
            ])),
        ]);
        $searchRequestTransfer = $this->tester->haveSearchRequestTransfer([SearchRequestTransfer::FACETS => $facetCollection]);

        // Act
        $filtersString = $filterConverter->convertFacetCollectionTransferToAlgoliaFiltersString($searchRequestTransfer);

        // Assert
        $this->assertEquals('price>=0 AND rating>=100.000000', $filtersString);
    }

    /**
     * @return void
     */
    public function testConvertFacetCollectionTransferToAlgoliaFiltersStringWhereFromMoreThenToAttributeReturnsCorrectResultForRangeFacetNotFromAttributes()
    {
        // Arrange
        $this->tester->haveCacheAdapterMock(static::ID_TENANT, ['rating', 'attributes.facet2', 'prices']);
        $filterConverter = $this->tester->getFactory()->createFilterConverter();
        $facetCollection = $this->tester->haveFacetCollectionTransfer([
            'rating' => $this->tester->haveFacetEntryTransfer('range', $this->tester->haveFacetParametersTransfer([
                FacetParametersTransfer::FROM => 100,
                FacetParametersTransfer::TO => 25,
                FacetParametersTransfer::VALUES => null,
            ])),
        ]);
        $searchRequestTransfer = $this->tester->haveSearchRequestTransfer([SearchRequestTransfer::FACETS => $facetCollection]);

        // Act
        $filtersString = $filterConverter->convertFacetCollectionTransferToAlgoliaFiltersString($searchRequestTransfer);

        // Assert
        $this->assertEquals('price>=0 AND rating:100.000000 TO 25.000000', $filtersString);
    }

    /**
     * @return void
     */
    public function testConvertFacetCollectionTransferToAlgoliaFiltersStringReturnsCorrectResultForValuesFacet()
    {
        // Arrange
        $this->tester->haveCacheAdapterMock(static::ID_TENANT, ['attributes.facet1', 'attributes.facet3', 'prices']);
        $filterConverter = $this->tester->getFactory()->createFilterConverter();
        $facetCollection = $this->tester->haveFacetCollectionTransfer([
            'facet1' => $this->tester->haveFacetEntryTransfer('values', $this->tester->haveFacetParametersTransfer([
                FacetParametersTransfer::VALUES => ['facetValue1'],
            ])),
        ]);
        $searchRequestTransfer = $this->tester->haveSearchRequestTransfer([SearchRequestTransfer::FACETS => $facetCollection]);

        // Act
        $filtersString = $filterConverter->convertFacetCollectionTransferToAlgoliaFiltersString($searchRequestTransfer);

        // Assert
        $this->assertEquals('price>=0 AND attributes.facet1:\'facetValue1\'', $filtersString);
    }

    /**
     * @return void
     */
    public function testConvertFacetCollectionTransferToAlgoliaFiltersStringReturnsCorrectResultForValuesFacetWithMultipleValues()
    {
        // Arrange
        $this->tester->haveCacheAdapterMock(static::ID_TENANT, ['attributes.facet1', 'attributes.facet2']);
        $filterConverter = $this->tester->getFactory()->createFilterConverter();
        $facetCollection = $this->tester->haveFacetCollectionTransfer([
            'facet1' => $this->tester->haveFacetEntryTransfer('values', $this->tester->haveFacetParametersTransfer([
                FacetParametersTransfer::VALUES => ['facetValue1', 'facetValue2'],
            ])),
        ]);
        $searchRequestTransfer = $this->tester->haveSearchRequestTransfer([SearchRequestTransfer::FACETS => $facetCollection]);

        // Act
        $filtersString = $filterConverter->convertFacetCollectionTransferToAlgoliaFiltersString($searchRequestTransfer);

        // Assert
        $this->assertEquals('(attributes.facet1:\'facetValue1\' OR attributes.facet1:\'facetValue2\')', $filtersString);
    }

    /**
     * @return void
     */
    public function testConvertFacetCollectionTransferToAlgoliaFiltersStringReturnsCorrectResultForMultipleFacetsWithMultipleValues()
    {
        // Arrange
        $this->tester->haveCacheAdapterMock(static::ID_TENANT, ['attributes.facet1', 'attributes.facet2', 'prices']);
        $filterConverter = $this->tester->getFactory()->createFilterConverter();
        $facetCollection = $this->tester->haveFacetCollectionTransfer([
            'facet1' => $this->tester->haveFacetEntryTransfer('range', $this->tester->haveFacetParametersTransfer([
                FacetParametersTransfer::FROM => 100,
                FacetParametersTransfer::TO => 250,
            ])),
            'facet2' => $this->tester->haveFacetEntryTransfer('values', $this->tester->haveFacetParametersTransfer([
                FacetParametersTransfer::VALUES => ['facetValue1', 'facetValue2'],
            ])),
        ]);
        $searchRequestTransfer = $this->tester->haveSearchRequestTransfer([SearchRequestTransfer::FACETS => $facetCollection]);

        // Act
        $filtersString = $filterConverter->convertFacetCollectionTransferToAlgoliaFiltersString($searchRequestTransfer);

        // Assert
        $this->assertEquals('price>=0 AND attributes.facet1:100.000000 TO 250.000000 AND (attributes.facet2:\'facetValue1\' OR attributes.facet2:\'facetValue2\')', $filtersString);
    }

    /**
     * @return void
     */
    public function testConvertFacetCollectionTransferWithEmptyFacetValuesToAlgoliaFiltersStringReturnsCorrectResultForMultipleFacetsWithValues()
    {
        // Arrange
        $this->tester->haveCacheAdapterMock(static::ID_TENANT, ['attributes.facet1', 'attributes.facet2']);
        $filterConverter = $this->tester->getFactory()->createFilterConverter();
        $facetCollection = $this->tester->haveFacetCollectionTransfer([
            'facet1' => $this->tester->haveFacetEntryTransfer('range', $this->tester->haveFacetParametersTransfer([
                FacetParametersTransfer::FROM => 100,
                FacetParametersTransfer::TO => 250,
            ])),
            'facet2' => $this->tester->haveFacetEntryTransfer('values', $this->tester->haveFacetParametersTransfer([
                FacetParametersTransfer::VALUES => [],
            ])),
        ]);
        $searchRequestTransfer = $this->tester->haveSearchRequestTransfer([SearchRequestTransfer::FACETS => $facetCollection]);

        // Act
        $filtersString = $filterConverter->convertFacetCollectionTransferToAlgoliaFiltersString($searchRequestTransfer);

        // Assert
        $this->assertEquals('attributes.facet1:100.000000 TO 250.000000', $filtersString);
    }

    /**
     * @return void
     */
    public function testConvertEmptyFacetCollectionTransferToAlgoliaFiltersStringReturnsEmptyString()
    {
        // Arrange
        $this->tester->haveCacheAdapterMock(static::ID_TENANT, ['attributes.facet1', 'attributes.facet2', 'prices']);
        $filterConverter = $this->tester->getFactory()->createFilterConverter();
        $searchRequestTransfer = $this->tester->haveSearchRequestTransfer();

        // Act
        $filtersString = $filterConverter->convertFacetCollectionTransferToAlgoliaFiltersString($searchRequestTransfer);

        // Assert
        $this->assertEquals('', $filtersString);
    }

    /**
     * @return void
     */
    public function testConvertFacetCollectionTransferToAlgoliaFiltersStringReturnsCorrectResultForMultipleFacetsWithMultipleValuesFromIndex()
    {
        // Arrange
        $this->tester->haveSearchIndexResolver(['attributesForFaceting' => ['afterDistinct(sorting(attributes.facet1))', 'attributes.facet2', 'attributes.facet3', '??', 'prices']]);
        $this->tester->haveCacheAdapterMock(static::ID_TENANT);
        $filterConverter = $this->tester->getFactory()->createFilterConverter();
        $facetCollection = $this->tester->haveFacetCollectionTransfer([
            'facet1' => $this->tester->haveFacetEntryTransfer('range', $this->tester->haveFacetParametersTransfer([
                FacetParametersTransfer::FROM => 100,
                FacetParametersTransfer::TO => 250,
            ])),
            'facet2' => $this->tester->haveFacetEntryTransfer('values', $this->tester->haveFacetParametersTransfer([
                FacetParametersTransfer::VALUES => ['facetValue1', 'facetValue2'],
            ])),
        ]);
        $searchRequestTransfer = $this->tester->haveSearchRequestTransfer([SearchRequestTransfer::FACETS => $facetCollection]);

        // Act
        $filtersString = $filterConverter->convertFacetCollectionTransferToAlgoliaFiltersString($searchRequestTransfer);

        // Assert
        $this->assertEquals('price>=0 AND attributes.facet1:100.000000 TO 250.000000 AND (attributes.facet2:\'facetValue1\' OR attributes.facet2:\'facetValue2\')', $filtersString);
    }
}
