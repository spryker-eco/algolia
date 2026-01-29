<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEcoTest\Zed\Algolia\Business\Api\Response\Extractor;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\FacetCollectionTransfer;
use Generated\Shared\Transfer\FacetEntryTransfer;
use Generated\Shared\Transfer\FacetParametersTransfer;
use Generated\Shared\Transfer\SearchRequestTransfer;
use SprykerEcoTest\Zed\Algolia\AlgoliaBusinessTester;

/**
 * Auto-generated group annotations
 *
 * @group PyzTest
 * @group Zed
 * @group Algolia
 * @group Business
 * @group Api
 * @group Response
 * @group Extractor
 * @group FacetsExtractorTest
 * Add your own group annotations below this line
 */
class FacetsExtractorTest extends Unit
{
    /**
     * @var \SprykerEcoTest\Zed\Algolia\AlgoliaBusinessTester
     */
    protected AlgoliaBusinessTester $tester;

    /**
     * @return void
     */
    public function testFacetsExtractedWhenTheyExistInResponseData(): void
    {
        // Arrange
        $normalResponseFixtures = $this->tester->loadNormalSearchResponseFixtures();
        $facetsExtractor = $this->tester->getFactory()->createFacetsExtractor();
        $searchRequestTransfer = $this->tester->haveSearchRequestTransfer([
            SearchRequestTransfer::FACETS => (new FacetCollectionTransfer())
                ->addFacet('currency', (new FacetEntryTransfer())->setType('values')->setParameters((new FacetParametersTransfer())->setValues(['eur'])))
                ->addFacet('price_mode', (new FacetEntryTransfer())->setType('values')->setParameters((new FacetParametersTransfer())->setValues(['GROSS_MODE']))),
        ]);

        // Act
        $extractedFacets = $facetsExtractor->extract($normalResponseFixtures, $searchRequestTransfer);

        // Assert
        $this->assertSame(3, count($extractedFacets));
        $this->assertEquals(['category', 'rating', 'price'], array_keys($extractedFacets));
        $this->assertSame(
            $normalResponseFixtures->getSearchResults()['facets']['category'],
            $extractedFacets['category'],
        );
        $this->assertSame(
            [
                'from' => $normalResponseFixtures->getSearchResults()['facets_stats']['rating']['min'],
                'to' => $normalResponseFixtures->getSearchResults()['facets_stats']['rating']['max'],
            ],
            $extractedFacets['rating'],
        );
        $this->assertSame(
            [
                'from' => $normalResponseFixtures->getSearchResults()['facets_stats']['prices.eur.gross']['min'],
                'to' => $normalResponseFixtures->getSearchResults()['facets_stats']['prices.eur.gross']['max'],
            ],
            $extractedFacets['price'],
        );
    }

    /**
     * @dataProvider filterableNameAttributesProvider
     *
     * @param array $configSettings
     *
     * @return void
     */
    public function testFacetsExtractedWithOrderFacetsCombination(array $configSettings): void
    {
        // Arrange
        $this->tester->mockConfigMethod('getFilterableNameAttributes', $configSettings);

        $normalResponseFixtures = $this->tester->loadNormalSearchResponseFixtures();
        $facetsExtractor = $this->tester->getFactory()->createFacetsExtractor();
        $searchRequestTransfer = $this->tester->haveSearchRequestTransfer();

        // Act
        $extractedFacets = $facetsExtractor->extract($normalResponseFixtures, $searchRequestTransfer);

        // Assert
        $this->assertSame(3, count($extractedFacets));
        $facets = array_keys($extractedFacets);
        foreach ($facets as $facet) {
            if (!in_array($facet, $configSettings)) {
                $configSettings[] = $facet;
            }
        }
        $this->assertEquals($configSettings, array_keys($extractedFacets));
    }

    /**
     * @return void
     */
    public function testFacetsExtractedWithOrderFacetsFromSettings(): void
    {
        // Arrange
        $this->tester->mockConfigMethod('getFilterableNameAttributes', ['category', 'rating', 'prices']);

        $normalResponseFixtures = $this->tester->loadNormalSearchResponseWithFacetOrderingFixtures();
        $facetsExtractor = $this->tester->getFactory()->createFacetsExtractor();
        $searchRequestTransfer = $this->tester->haveSearchRequestTransfer();

        // Act
        $extractedFacets = $facetsExtractor->extract($normalResponseFixtures, $searchRequestTransfer);

        // Assert
        $this->assertSame(4, count($extractedFacets));
        $this->assertEquals(['rating', 'category', 'price', 'search_metadata.key'], array_keys($extractedFacets));
    }

    /**
     * @return void
     */
    public function testFacetsExtractedWhenTheyDoNotExistInResponseData(): void
    {
        // Arrange
        $emptyResponseFixtures = $this->tester->loadEmptySearchResponseFixtures();
        $facetsExtractor = $this->tester->getFactory()->createFacetsExtractor();
        $searchRequestTransfer = (new SearchRequestTransfer());
        // Act
        $extractedFacets = $facetsExtractor->extract($emptyResponseFixtures, $searchRequestTransfer);

        //Assert
        $this->assertSame(0, count($extractedFacets));
    }

    /**
     * @return array<string>
     */
    protected function filterableNameAttributesProvider(): array
    {
        return [
            [['category', 'price', 'rating']],
            [['price', 'rating', 'category']],
            [['rating', 'category', 'price']],
            [['price', 'category']],
        ];
    }
}
