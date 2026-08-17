<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEcoTest\Client\Algolia\Formatter;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\SearchResponsePaginationTransfer;
use Generated\Shared\Transfer\SearchResponseTransfer;
use Spryker\Client\SearchExtension\Dependency\Plugin\ResultFormatterPluginInterface;
use SprykerEcoTest\Client\Algolia\AlgoliaClientTester;

/**
 * Result counts are obtained by calling search() with no result formatters and reading the pagination
 * off the response, which is what every tab on the full text search page renders its count from.
 *
 * Auto-generated group annotations
 *
 * @group SprykerEcoTest
 * @group Client
 * @group Algolia
 * @group Formatter
 * @group SearchResponseFormatterTest
 * Add your own group annotations below this line
 */
class SearchResponseFormatterTest extends Unit
{
    /**
     * @var string
     */
    protected const DEFAULT_PAGINATION_FORMATTER = 'pagination';

    /**
     * @var string
     */
    protected const FORMATTER_NAME = 'products';

    /**
     * @var int
     */
    protected const NUM_FOUND = 5;

    /**
     * @var \SprykerEcoTest\Client\Algolia\AlgoliaClientTester
     */
    protected AlgoliaClientTester $tester;

    /**
     * A count call passes no result formatters, and its callers do pass request parameters. Branching
     * on the parameters sent this into the formatter loop, which iterated an empty array and returned
     * an empty result, so the caller read the count as null and rendered 0.
     *
     * @return void
     */
    public function testPaginationIsReturnedWhenNoResultFormattersAreGivenAndRequestParametersArePresent(): void
    {
        // Arrange
        $searchResponseFormatter = $this->tester->getFactory()->createSearchResponseFormatter();

        // Act
        $formattedResults = $searchResponseFormatter->format(
            $this->createSearchResponseTransfer(),
            [],
            ['q' => 'towel'],
        );

        // Assert
        $this->assertArrayHasKey(static::DEFAULT_PAGINATION_FORMATTER, $formattedResults);
        $this->assertSame(
            static::NUM_FOUND,
            $formattedResults[static::DEFAULT_PAGINATION_FORMATTER]->getNumFound(),
        );
    }

    /**
     * @return void
     */
    public function testPaginationIsReturnedWhenNoResultFormattersAndNoRequestParametersAreGiven(): void
    {
        // Arrange
        $searchResponseFormatter = $this->tester->getFactory()->createSearchResponseFormatter();

        // Act
        $formattedResults = $searchResponseFormatter->format($this->createSearchResponseTransfer(), [], []);

        // Assert
        $this->assertArrayHasKey(static::DEFAULT_PAGINATION_FORMATTER, $formattedResults);
        $this->assertSame(
            static::NUM_FOUND,
            $formattedResults[static::DEFAULT_PAGINATION_FORMATTER]->getNumFound(),
        );
    }

    /**
     * The inverse of the same defect: formatters given without request parameters were skipped and the
     * caller silently received pagination instead of the formatted items.
     *
     * @return void
     */
    public function testResultFormattersAreAppliedWhenNoRequestParametersAreGiven(): void
    {
        // Arrange
        $searchResponseFormatter = $this->tester->getFactory()->createSearchResponseFormatter();

        // Act
        $formattedResults = $searchResponseFormatter->format(
            $this->createSearchResponseTransfer(),
            [$this->createResultFormatterPluginMock()],
            [],
        );

        // Assert
        $this->assertArrayHasKey(static::FORMATTER_NAME, $formattedResults);
        $this->assertArrayNotHasKey(static::DEFAULT_PAGINATION_FORMATTER, $formattedResults);
    }

    /**
     * @return void
     */
    public function testResultFormattersAreAppliedWhenRequestParametersAreGiven(): void
    {
        // Arrange
        $searchResponseFormatter = $this->tester->getFactory()->createSearchResponseFormatter();

        // Act
        $formattedResults = $searchResponseFormatter->format(
            $this->createSearchResponseTransfer(),
            [$this->createResultFormatterPluginMock()],
            ['q' => 'towel'],
        );

        // Assert
        $this->assertArrayHasKey(static::FORMATTER_NAME, $formattedResults);
    }

    protected function createSearchResponseTransfer(): SearchResponseTransfer
    {
        return (new SearchResponseTransfer())
            ->setItems([])
            ->setFacets([])
            ->setPagination(
                (new SearchResponsePaginationTransfer())
                    ->setNumFound(static::NUM_FOUND)
                    ->setCurrentPage(1)
                    ->setCurrentItemsPerPage(12),
            );
    }

    protected function createResultFormatterPluginMock(): ResultFormatterPluginInterface
    {
        $resultFormatterPluginMock = $this->createMock(ResultFormatterPluginInterface::class);
        $resultFormatterPluginMock->method('getName')->willReturn(static::FORMATTER_NAME);
        $resultFormatterPluginMock->method('formatResult')->willReturn(['formatted']);

        return $resultFormatterPluginMock;
    }
}
