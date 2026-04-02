<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types = 1);

namespace SprykerEcoTest\Zed\Algolia\Business\Configuration;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\AlgoliaApiCredentialsValidationTransfer;
use Generated\Shared\Transfer\ConfigurationValueCollectionRequestTransfer;
use Generated\Shared\Transfer\ConfigurationValueDeletionTransfer;
use Generated\Shared\Transfer\ConfigurationValueTransfer;
use SprykerEco\Zed\Algolia\AlgoliaConfig;
use SprykerEco\Zed\Algolia\Business\Configuration\CredentialsPreSaveHandler;
use SprykerEco\Zed\Algolia\Business\Validator\ApiCredentialsValidatorInterface;
use SprykerEco\Zed\Algolia\Communication\Constraint\AlgoliaCredentialsConstraint;
use SprykerEco\Zed\Algolia\Communication\Constraint\AlgoliaCredentialsRemovalConstraint;
use SprykerEcoTest\Zed\Algolia\AlgoliaBusinessTester;

/**
 * @group SprykerEcoTest
 * @group Zed
 * @group Algolia
 * @group Business
 * @group Configuration
 * @group CredentialsPreSaveHandlerTest
 * Add your own group annotations below this line
 */
class CredentialsPreSaveHandlerTest extends Unit
{
    protected const string APP_ID = 'test-app-id';

    protected const string ADMIN_API_KEY = 'test-admin-key';

    protected const string SEARCH_ONLY_API_KEY = 'test-search-only-key';

    protected AlgoliaBusinessTester $tester;

    /**
     * @dataProvider deletionKeyNormalizationDataProvider
     */
    public function testDeletionKeyNormalization(
        ?string $settingKey,
        int $expectedConfigValuesCount,
        ?string $expectedSettingKey,
        ?string $expectedValue,
    ): void {
        // Arrange
        $handler = $this->createHandler();
        $deletionKey = (new ConfigurationValueDeletionTransfer())->setSettingKey($settingKey);
        $request = (new ConfigurationValueCollectionRequestTransfer())->addDeletionKey($deletionKey);

        // Act
        $result = $handler->handleCredentialsPreSave($request);

        // Assert
        $this->assertCount($expectedConfigValuesCount, $result->getConfigurationValues());

        if ($expectedSettingKey !== null) {
            $this->assertSame($expectedSettingKey, $result->getConfigurationValues()->offsetGet(0)->getSettingKey());
            $this->assertSame($expectedValue, $result->getConfigurationValues()->offsetGet(0)->getValue());
        }
    }

    /**
     * @return array<string, array<mixed>>
     */
    public static function deletionKeyNormalizationDataProvider(): array
    {
        return [
            'null settingKey is skipped and no config value is added' => [
                'settingKey' => null,
                'expectedConfigValuesCount' => 0,
                'expectedSettingKey' => null,
                'expectedValue' => null,
            ],
            'non-credential settingKey is not converted and stays as deletion key' => [
                'settingKey' => 'some:unrelated:key',
                'expectedConfigValuesCount' => 0,
                'expectedSettingKey' => null,
                'expectedValue' => null,
            ],
            'credential settingKey is converted to a config value with empty string' => [
                'settingKey' => AlgoliaConfig::CONFIGURATION_KEY_APPLICATION_ID,
                'expectedConfigValuesCount' => 1,
                'expectedSettingKey' => AlgoliaConfig::CONFIGURATION_KEY_APPLICATION_ID,
                'expectedValue' => '',
            ],
        ];
    }

    /**
     * @dataProvider handleCredentialsPreSaveDataProvider
     *
     * @param array<string, string> $requestValues
     * @param array<string, bool>|null $validationResult
     * @param array<string, string> $expectedValues
     */
    public function testHandleCredentialsPreSave(
        array $requestValues,
        bool $searchForProductsEnabled,
        bool $searchForCmsEnabled,
        ?array $validationResult,
        array $expectedValues,
    ): void {
        // Arrange
        $configMock = $this->createConfigMock($searchForProductsEnabled, $searchForCmsEnabled);
        $validationTransfer = $validationResult !== null ? $this->buildValidationTransfer($validationResult) : null;
        $handler = $this->createHandler($configMock, $validationTransfer);

        $request = new ConfigurationValueCollectionRequestTransfer();

        foreach ($requestValues as $settingKey => $value) {
            $request->addConfigurationValue(
                (new ConfigurationValueTransfer())->setSettingKey($settingKey)->setValue($value),
            );
        }

        // Act
        $result = $handler->handleCredentialsPreSave($request);

        // Assert
        $valuesByKey = $this->indexConfigValuesByKey($result);

        foreach ($expectedValues as $settingKey => $expectedValue) {
            $this->assertSame($expectedValue, $valuesByKey[$settingKey]);
        }
    }

    /**
     * @return array<string, array<mixed>>
     */
    public static function handleCredentialsPreSaveDataProvider(): array
    {
        $inv = AlgoliaCredentialsConstraint::INVALID_SENTINEL;
        $rem = AlgoliaCredentialsRemovalConstraint::INVALID_SENTINEL;

        $appKey = AlgoliaConfig::CONFIGURATION_KEY_APPLICATION_ID;
        $searchKey = AlgoliaConfig::CONFIGURATION_KEY_SEARCH_ONLY_API_KEY;
        $adminKey = AlgoliaConfig::CONFIGURATION_KEY_ADMIN_API_KEY;

        $appId = static::APP_ID;
        $searchOnlyApiKey = static::SEARCH_ONLY_API_KEY;
        $adminApiKey = static::ADMIN_API_KEY;

        return [
            'no credential fields in request returns unchanged' => [
                'requestValues' => ['other:setting' => 'some-value'],
                'searchForProductsEnabled' => false,
                'searchForCmsEnabled' => false,
                'validationResult' => null,
                'expectedValues' => ['other:setting' => 'some-value'],
            ],
            'all credentials empty and both search providers disabled returns unchanged' => [
                'requestValues' => [$appKey => '', $searchKey => '', $adminKey => ''],
                'searchForProductsEnabled' => false,
                'searchForCmsEnabled' => false,
                'validationResult' => null,
                'expectedValues' => [$appKey => '', $searchKey => '', $adminKey => ''],
            ],
            'all credentials empty and product search enabled marks removal sentinel on all credential keys' => [
                'requestValues' => [$appKey => '', $searchKey => '', $adminKey => ''],
                'searchForProductsEnabled' => true,
                'searchForCmsEnabled' => false,
                'validationResult' => null,
                'expectedValues' => [$appKey => $rem, $searchKey => $rem, $adminKey => $rem],
            ],
            'all credentials empty and CMS search enabled marks removal sentinel on all credential keys' => [
                'requestValues' => [$appKey => '', $searchKey => '', $adminKey => ''],
                'searchForProductsEnabled' => false,
                'searchForCmsEnabled' => true,
                'validationResult' => null,
                'expectedValues' => [$appKey => $rem, $searchKey => $rem, $adminKey => $rem],
            ],
            'partial credentials filled marks invalid sentinel on all credential keys' => [
                'requestValues' => [$appKey => $appId, $searchKey => '', $adminKey => ''],
                'searchForProductsEnabled' => false,
                'searchForCmsEnabled' => false,
                'validationResult' => null,
                'expectedValues' => [$appKey => $inv, $searchKey => $inv, $adminKey => $inv],
            ],
            'all credentials filled and all valid returns original values' => [
                'requestValues' => [$appKey => $appId, $searchKey => $searchOnlyApiKey, $adminKey => $adminApiKey],
                'searchForProductsEnabled' => false,
                'searchForCmsEnabled' => false,
                'validationResult' => ['appIdValid' => true, 'searchKeyValid' => true, 'adminKeyValid' => true],
                'expectedValues' => [$appKey => $appId, $searchKey => $searchOnlyApiKey, $adminKey => $adminApiKey],
            ],
            'application ID invalid marks only application ID key with sentinel' => [
                'requestValues' => [$appKey => $appId, $searchKey => $searchOnlyApiKey, $adminKey => $adminApiKey],
                'searchForProductsEnabled' => false,
                'searchForCmsEnabled' => false,
                'validationResult' => ['appIdValid' => false, 'searchKeyValid' => true, 'adminKeyValid' => true],
                'expectedValues' => [$appKey => $inv, $searchKey => $searchOnlyApiKey, $adminKey => $adminApiKey],
            ],
            'search-only API key invalid marks only search key with sentinel' => [
                'requestValues' => [$appKey => $appId, $searchKey => $searchOnlyApiKey, $adminKey => $adminApiKey],
                'searchForProductsEnabled' => false,
                'searchForCmsEnabled' => false,
                'validationResult' => ['appIdValid' => true, 'searchKeyValid' => false, 'adminKeyValid' => true],
                'expectedValues' => [$appKey => $appId, $searchKey => $inv, $adminKey => $adminApiKey],
            ],
            'admin API key invalid marks only admin key with sentinel' => [
                'requestValues' => [$appKey => $appId, $searchKey => $searchOnlyApiKey, $adminKey => $adminApiKey],
                'searchForProductsEnabled' => false,
                'searchForCmsEnabled' => false,
                'validationResult' => ['appIdValid' => true, 'searchKeyValid' => true, 'adminKeyValid' => false],
                'expectedValues' => [$appKey => $appId, $searchKey => $searchOnlyApiKey, $adminKey => $inv],
            ],
            'multiple fields invalid marks all invalid fields with sentinel' => [
                'requestValues' => [$appKey => $appId, $searchKey => $searchOnlyApiKey, $adminKey => $adminApiKey],
                'searchForProductsEnabled' => false,
                'searchForCmsEnabled' => false,
                'validationResult' => ['appIdValid' => false, 'searchKeyValid' => false, 'adminKeyValid' => true],
                'expectedValues' => [$appKey => $inv, $searchKey => $inv, $adminKey => $adminApiKey],
            ],
            'non-credential fields are not affected by sentinel' => [
                'requestValues' => [$appKey => $appId, $searchKey => $searchOnlyApiKey, $adminKey => $adminApiKey, 'other:key' => 'should-stay'],
                'searchForProductsEnabled' => false,
                'searchForCmsEnabled' => false,
                'validationResult' => ['appIdValid' => false, 'searchKeyValid' => false, 'adminKeyValid' => false],
                'expectedValues' => ['other:key' => 'should-stay'],
            ],
        ];
    }

    public function testHandleCredentialsPreSaveWhenCredentialDeletionKeyNormalizedToEmptyAndSearchProviderDisabledReturnsUnchanged(): void
    {
        // Arrange
        $configMock = $this->createConfigMock(searchForProductsEnabled: false, searchForCmsEnabled: false);
        $handler = $this->createHandler(configMock: $configMock);
        $deletionKey = (new ConfigurationValueDeletionTransfer())
            ->setSettingKey(AlgoliaConfig::CONFIGURATION_KEY_APPLICATION_ID);
        $request = (new ConfigurationValueCollectionRequestTransfer())->addDeletionKey($deletionKey);

        // Act
        $result = $handler->handleCredentialsPreSave($request);

        // Assert
        $this->assertCount(1, $result->getConfigurationValues());
        $this->assertSame('', $result->getConfigurationValues()->offsetGet(0)->getValue());
    }

    protected function createHandler(
        ?AlgoliaConfig $configMock = null,
        ?AlgoliaApiCredentialsValidationTransfer $validationTransfer = null,
    ): CredentialsPreSaveHandler {
        $configMock ??= $this->createConfigMock();
        $validatorMock = $this->createMock(ApiCredentialsValidatorInterface::class);

        if ($validationTransfer !== null) {
            $validatorMock->method('validate')->willReturn($validationTransfer);
        }

        return new CredentialsPreSaveHandler($configMock, $validatorMock);
    }

    protected function createConfigMock(
        bool $searchForProductsEnabled = false,
        bool $searchForCmsEnabled = false,
    ): AlgoliaConfig {
        $configMock = $this->createMock(AlgoliaConfig::class);

        $configMock->method('getApplicationId')->willReturn('');
        $configMock->method('getSearchOnlyApiKey')->willReturn('');
        $configMock->method('getAdminApiKey')->willReturn('');
        $configMock->method('isSearchInFrontendEnabledForProducts')->willReturn($searchForProductsEnabled);
        $configMock->method('isSearchInFrontendEnabledForCmsPages')->willReturn($searchForCmsEnabled);

        return $configMock;
    }

    /**
     * @param array<string, bool> $validationResult
     */
    protected function buildValidationTransfer(array $validationResult): AlgoliaApiCredentialsValidationTransfer
    {
        return (new AlgoliaApiCredentialsValidationTransfer())
            ->setIsAccountIdValid($validationResult['appIdValid'])
            ->setIsSearchOnlyApiKeyValid($validationResult['searchKeyValid'])
            ->setIsAdminApiKeyValid($validationResult['adminKeyValid']);
    }

    /**
     * @return array<string, string>
     */
    protected function indexConfigValuesByKey(ConfigurationValueCollectionRequestTransfer $transfer): array
    {
        $result = [];

        foreach ($transfer->getConfigurationValues() as $configValue) {
            $result[$configValue->getSettingKey()] = $configValue->getValue();
        }

        return $result;
    }
}
