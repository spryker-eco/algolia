<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEcoTest\Zed\Algolia\Business\Api\IndexConfigurator;

use Algolia\AlgoliaSearch\Api\SearchClient;
use Codeception\Test\Unit;
use Generated\Shared\Transfer\AlgoliaConfigTransfer;
use ReflectionClass;

/**
 * Auto-generated group annotations
 *
 * @group SprykerEcoTest
 * @group Zed
 * @group Algolia
 * @group Business
 * @group Api
 * @group IndexConfigurator
 * @group IndexConfiguratorTest
 * Add your own group annotations below this line
 */
class IndexConfiguratorTest extends Unit
{
    /**
     * @var string
     */
    protected const TEST_INDEX_NAME = 'testIndexName';

    /**
     * @var string
     */
    protected const TEST_LOCALE_DEFAULT = 'en_US';

    /**
     * @var \SprykerEcoTest\Zed\Algolia\AlgoliaBusinessTester
     */
    protected $tester;

    /**
     * @dataProvider localesProvider
     */
    public function testConfigureIndexCorrectlyParsesLocales(string $locale, string $expectedLanguage, bool $withPrices): void
    {
        // Arrange
        $searchClientMock = $this->tester->createSearchClientMock();

        $callCount = 0;
        $searchClientMock
            ->expects($this->atLeast(2))
            ->method('setSettings')
            ->willReturnCallback(function ($indexName, $settings, $forwardToReplicas = false) use (&$callCount, $expectedLanguage) {
                $callCount++;

                // Second call should have the language settings
                if ($callCount === 2) {
                    $this->assertIsArray($settings);
                    $this->assertArrayHasKey('queryLanguages', $settings);
                    $this->assertArrayHasKey('indexLanguages', $settings);
                    $this->assertEquals([$expectedLanguage], $settings['queryLanguages']);
                    $this->assertEquals([$expectedLanguage], $settings['indexLanguages']);
                    $this->assertTrue($forwardToReplicas);
                }

                return ['taskID' => 1];
            });

        $searchClientMock->method('waitForTask')->willReturn(null);

        $this->tester->mockFactoryMethod('createSuggestionIndexHandler', $this->tester->mockSuggestionIndexHandler());
        $algoliaConfigTransfer = $this->tester->haveAlgoliaConfigTransfer([AlgoliaConfigTransfer::IS_PRODUCT_PRICE_SYNCED => $withPrices]);
        $indexConfigurator = $this->tester->getFactory()->createIndexConfigurator();

        // Act
        $indexConfigurator->configureIndex(static::TEST_INDEX_NAME, $searchClientMock, $locale, $algoliaConfigTransfer);

        // Assert is part of the expected method call
    }

    /**
     * @return array<string, array<int, string>>
     */
    protected function localesProvider(): array
    {
        return [
            'valid locale with dash' => [
                'en-US',
                'en',
                true,
            ],
            'valid locale with underscore' => [
                'en_US',
                'en',
                true,
            ],
            'invalid locale with no delimiters' => [
                'locale',
                'locale',
                false,
            ],
            'empty locale' => [
                '',
                'en', // this will vary depending on your chosen system locale (see https://www.php.net/locale.getdefault)
                true,
            ],
        ];
    }

    public function testGetReplicaNamesWithRankingAttributesReturnsCorrectStructureWithPrices(): void
    {
        // Arrange
        $algoliaConfigTransfer = (new AlgoliaConfigTransfer())
            ->setIsProductPriceSynced(true);

        $indexConfigurator = $this->tester->getFactory()->createIndexConfigurator();

        // Act
        $reflection = new ReflectionClass($indexConfigurator);
        $method = $reflection->getMethod('getReplicaNamesWithRankingAttributes');
        $method->setAccessible(true);
        $result = $method->invoke($indexConfigurator, static::TEST_INDEX_NAME, $algoliaConfigTransfer);

        // Assert
        $this->assertIsArray($result);
        $this->assertNotEmpty($result);

        // Check that price-related replicas are included when productsWithoutPrice is false
        $replicaNames = array_keys($result);
        $priceRelatedReplicas = array_filter($replicaNames, function ($name) {
            return strpos($name, 'prices.eur.gross') !== false || strpos($name, 'prices.eur.net') !== false;
        });
        $this->assertNotEmpty($priceRelatedReplicas, 'Price-related replicas should be present when productsWithoutPrice is false');

        // Check that each replica has proper ranking attributes structure
        foreach ($result as $replicaName => $rankingAttributes) {
            $this->assertIsArray($rankingAttributes);
            $this->assertNotEmpty($rankingAttributes);
            $this->assertStringContainsString(static::TEST_INDEX_NAME, $replicaName);
        }
    }

    public function testGetReplicaNamesWithRankingAttributesExcludesPricesWhenConfigured(): void
    {
        // Arrange
        $algoliaConfigTransfer = (new AlgoliaConfigTransfer())
            ->setIsProductPriceSynced(false);

        $indexConfigurator = $this->tester->getFactory()->createIndexConfigurator();

        // Act
        $reflection = new ReflectionClass($indexConfigurator);
        $method = $reflection->getMethod('getReplicaNamesWithRankingAttributes');
        $method->setAccessible(true);
        $result = $method->invoke($indexConfigurator, static::TEST_INDEX_NAME, $algoliaConfigTransfer);

        // Assert
        $this->assertIsArray($result);
        $this->assertNotEmpty($result);

        // Check that price-related replicas are excluded when productsWithoutPrice is true
        $replicaNames = array_keys($result);
        $priceRelatedReplicas = array_filter($replicaNames, function ($name) {
            return strpos($name, 'prices.eur.gross') !== false || strpos($name, 'prices.eur.net') !== false;
        });
        $this->assertEmpty($priceRelatedReplicas, 'Price-related replicas should not be present when productsWithoutPrice is true');

        // Check that basic replicas (rating, name) are still present
        $basicReplicas = array_filter($replicaNames, function ($name) {
            return strpos($name, 'rating') !== false || strpos($name, 'name') !== false;
        });
        $this->assertNotEmpty($basicReplicas, 'Basic replicas (rating, name) should always be present');
    }

    public function testGetReplicaNamesWithRankingAttributesIncludesDefaultRankingOrder(): void
    {
        // Arrange
        $algoliaConfigTransfer = (new AlgoliaConfigTransfer())
            ->setIsProductPriceSynced(true);

        $indexConfigurator = $this->tester->getFactory()->createIndexConfigurator();

        // Act
        $reflection = new ReflectionClass($indexConfigurator);
        $method = $reflection->getMethod('getReplicaNamesWithRankingAttributes');
        $method->setAccessible(true);
        $result = $method->invoke($indexConfigurator, static::TEST_INDEX_NAME, $algoliaConfigTransfer);

        // Assert
        $expectedDefaultRankingItems = ['typo', 'geo', 'words', 'filters', 'proximity', 'attribute', 'exact', 'custom'];

        foreach ($result as $replicaName => $rankingAttributes) {
            // Each ranking attributes array should contain default ranking order items
            foreach ($expectedDefaultRankingItems as $expectedItem) {
                $this->assertContains(
                    $expectedItem,
                    $rankingAttributes,
                    sprintf("Replica '%s' should contain default ranking item '%s'", $replicaName, $expectedItem),
                );
            }
        }
    }
}
