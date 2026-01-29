<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEcoTest\Zed\Algolia\Business\Mapper;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\AlgoliaApiCredentialsTransfer;

/**
 * Auto-generated group annotations
 *
 * @group PyzTest
 * @group Zed
 * @group Algolia
 * @group Business
 * @group Mapper
 * @group CredentialsMapperTest
 * Add your own group annotations below this line
 */
class CredentialsMapperTest extends Unit
{
    /**
     * @var \SprykerEcoTest\Zed\Algolia\AlgoliaBusinessTester
     */
    protected $tester;

    /**
     * @return void
     */
    public function mapAlgoliaSearchOnlyCredentialsToAlgoliaCredentialsTransfer(): void
    {
        // Arrange
        $algoliaConfigTransfer = $this->tester->haveAlgoliaConfigTransfer();
        $credentialsMapper = $this->tester->getFactory()->createCredentialsMapper();

        // Act
        $algoliaApiCredentialsTransfer = $credentialsMapper
            ->mapAlgoliaSearchOnlyCredentialsToAlgoliaApiCredentialsTransfer(
                $algoliaConfigTransfer,
                new AlgoliaApiCredentialsTransfer(),
            );

        // Assert
        $this->assertEquals($algoliaConfigTransfer->getApplicationId(), $algoliaApiCredentialsTransfer->getApplicationId());
        $this->assertEquals($algoliaConfigTransfer->getSearchOnlyApiKey(), $algoliaApiCredentialsTransfer->getApiKey());
    }
}
