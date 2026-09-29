<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEcoTest\Zed\Algolia\Business\Expander;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\AlgoliaProductObjectTransfer;
use SprykerEcoTest\Zed\Algolia\AlgoliaBusinessTester;

/**
 * Auto-generated group annotations
 *
 * @group SprykerEcoTest
 * @group Zed
 * @group Algolia
 * @group Business
 * @group Expander
 * @group MultiValueAttributeExpanderTest
 * Add your own group annotations below this line
 */
class MultiValueAttributeExpanderTest extends Unit
{
    protected AlgoliaBusinessTester $tester;

    public function testLeavesAttributesUntouchedWhenNoMultiValueAttributeIsConfigured(): void
    {
        // Arrange
        $attributes = ['color' => 'red,blue', 'brand' => 'Acme'];
        $this->tester->mockConfigMethod('getMultiValueProductAttributeDelimiters', []);

        // Act
        $algoliaProductObjectTransfer = $this->expandAttributes($attributes);

        // Assert
        $this->assertSame($attributes, $algoliaProductObjectTransfer->getAttributes());
    }

    public function testSplitsAConfiguredAttributeIntoOneValuePerFacetBucket(): void
    {
        // Arrange
        $this->tester->mockConfigMethod('getMultiValueProductAttributeDelimiters', ['color' => ',']);

        // Act
        $algoliaProductObjectTransfer = $this->expandAttributes(['color' => 'red,blue']);

        // Assert
        $this->assertSame(['red', 'blue'], $algoliaProductObjectTransfer->getAttributes()['color']);
    }

    public function testSplitsEachAttributeByItsOwnDelimiter(): void
    {
        // Arrange
        $this->tester->mockConfigMethod('getMultiValueProductAttributeDelimiters', [
            'color' => ',',
            'size' => '|',
        ]);

        // Act
        $attributes = $this->expandAttributes([
            'color' => 'red,blue',
            'size' => '5,5Gb|6,5Gb',
        ])->getAttributes();

        // Assert
        $this->assertSame(['red', 'blue'], $attributes['color']);
        $this->assertSame(['5,5Gb', '6,5Gb'], $attributes['size']);
    }

    public function testCollapsesBothSpellingsOfTheSameListIntoTheSameValues(): void
    {
        // Arrange
        $this->tester->mockConfigMethod('getMultiValueProductAttributeDelimiters', ['material' => ',']);

        // Act
        $spacedAttributes = $this->expandAttributes(['material' => 'cotton, wool'])->getAttributes();
        $unspacedAttributes = $this->expandAttributes(['material' => 'cotton,wool'])->getAttributes();

        // Assert
        $this->assertSame(['cotton', 'wool'], $spacedAttributes['material']);
        $this->assertSame($spacedAttributes['material'], $unspacedAttributes['material']);
    }

    public function testLeavesAValueWithoutTheDelimiterAsAString(): void
    {
        // Arrange
        $this->tester->mockConfigMethod('getMultiValueProductAttributeDelimiters', ['color' => ',']);

        // Act
        $algoliaProductObjectTransfer = $this->expandAttributes(['color' => 'red']);

        // Assert
        $this->assertSame('red', $algoliaProductObjectTransfer->getAttributes()['color']);
    }

    public function testLeavesAnAttributeThatIsAlreadyAnArrayUntouched(): void
    {
        // Arrange
        $this->tester->mockConfigMethod('getMultiValueProductAttributeDelimiters', ['color' => ',']);

        // Act
        $algoliaProductObjectTransfer = $this->expandAttributes(['color' => ['red', 'blue']]);

        // Assert
        $this->assertSame(['red', 'blue'], $algoliaProductObjectTransfer->getAttributes()['color']);
    }

    public function testLeavesAConfiguredAttributeThatIsNotStringValuedUntouched(): void
    {
        // Arrange
        $this->tester->mockConfigMethod('getMultiValueProductAttributeDelimiters', [
            'megapixel' => ',',
            'is_gift' => ',',
            'size' => ',',
        ]);

        // Act
        $attributes = $this->expandAttributes([
            'megapixel' => 12,
            'is_gift' => true,
            'size' => null,
        ])->getAttributes();

        // Assert
        $this->assertSame(12, $attributes['megapixel']);
        $this->assertTrue($attributes['is_gift']);
        $this->assertNull($attributes['size']);
    }

    public function testIgnoresAConfiguredAttributeThatTheProductDoesNotCarry(): void
    {
        // Arrange
        $this->tester->mockConfigMethod('getMultiValueProductAttributeDelimiters', ['color' => ',']);

        // Act
        $algoliaProductObjectTransfer = $this->expandAttributes(['brand' => 'Acme']);

        // Assert
        $this->assertSame(['brand' => 'Acme'], $algoliaProductObjectTransfer->getAttributes());
    }

    public function testDoesNotSplitAnAttributeThatIsNotConfigured(): void
    {
        // Arrange
        $careInstructions = 'Machine wash cold, tumble dry low.';
        $this->tester->mockConfigMethod('getMultiValueProductAttributeDelimiters', ['color' => ',']);

        // Act
        $attributes = $this->expandAttributes([
            'weight' => '1,5 kg',
            'care_instructions' => $careInstructions,
        ])->getAttributes();

        // Assert
        $this->assertSame('1,5 kg', $attributes['weight']);
        $this->assertSame($careInstructions, $attributes['care_instructions']);
    }

    public function testUsesTheConfiguredDelimiter(): void
    {
        // Arrange
        $this->tester->mockConfigMethod('getMultiValueProductAttributeDelimiters', ['weight' => '|']);

        // Act
        $attributes = $this->expandAttributes(['weight' => '1,5 kg | 2,5 kg'])->getAttributes();

        // Assert
        $this->assertSame(['1,5 kg', '2,5 kg'], $attributes['weight']);
    }

    public function testReturnsTheGivenTransferInstance(): void
    {
        // Arrange
        $this->tester->mockConfigMethod('getMultiValueProductAttributeDelimiters', ['color' => ',']);
        $algoliaProductObjectTransfer = (new AlgoliaProductObjectTransfer())
            ->setAttributes(['color' => 'red,blue']);

        // Act
        $expandedAlgoliaProductObjectTransfer = $this->tester->getFactory()
            ->createMultiValueAttributeExpander()
            ->expandAlgoliaProductObjectWithMultiValueAttributes($algoliaProductObjectTransfer);

        // Assert
        $this->assertSame($algoliaProductObjectTransfer, $expandedAlgoliaProductObjectTransfer);
    }

    /**
     * @param array<string, mixed> $attributes
     */
    protected function expandAttributes(array $attributes): AlgoliaProductObjectTransfer
    {
        return $this->tester->getFactory()
            ->createMultiValueAttributeExpander()
            ->expandAlgoliaProductObjectWithMultiValueAttributes(
                (new AlgoliaProductObjectTransfer())->setAttributes($attributes),
            );
    }
}
