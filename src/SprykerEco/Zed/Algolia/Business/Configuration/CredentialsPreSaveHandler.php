<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types = 1);

namespace SprykerEco\Zed\Algolia\Business\Configuration;

use Generated\Shared\Transfer\AlgoliaConfigTransfer;
use Generated\Shared\Transfer\ConfigurationValueCollectionRequestTransfer;
use Generated\Shared\Transfer\ConfigurationValueTransfer;
use SprykerEco\Zed\Algolia\AlgoliaConfig;
use SprykerEco\Zed\Algolia\Business\Validator\ApiCredentialsValidatorInterface;
use SprykerEco\Zed\Algolia\Communication\Constraint\AlgoliaCredentialsConstraint;
use SprykerEco\Zed\Algolia\Communication\Constraint\AlgoliaCredentialsRemovalConstraint;

class CredentialsPreSaveHandler implements CredentialsPreSaveHandlerInterface
{
    protected const string DEFAULT_CREDENTIAL_VALUE = '';

    public function __construct(
        protected AlgoliaConfig $algoliaConfig,
        protected ApiCredentialsValidatorInterface $apiCredentialsValidator,
    ) {
    }

    public function handleCredentialsPreSave(
        ConfigurationValueCollectionRequestTransfer $configurationValueCollectionRequestTransfer,
    ): ConfigurationValueCollectionRequestTransfer {
        $configurationValueCollectionRequestTransfer = $this->normalizeDeletionKeysToEmptyValues($configurationValueCollectionRequestTransfer);

        if (!$this->hasCredentialFieldsInRequest($configurationValueCollectionRequestTransfer)) {
            return $configurationValueCollectionRequestTransfer;
        }

        $algoliaConfigTransfer = $this->buildAlgoliaConfigTransfer($configurationValueCollectionRequestTransfer);

        if ($this->areAllCredentialsEmpty($algoliaConfigTransfer)) {
            if (!$this->isAlgoliaSearchProviderEnabled()) {
                return $configurationValueCollectionRequestTransfer;
            }

            return $this->markConfigurationValuesAsInvalid(
                $configurationValueCollectionRequestTransfer,
                AlgoliaCredentialsRemovalConstraint::INVALID_SENTINEL,
                $this->algoliaConfig->getAlgoliaCredentialsKeys(),
            );
        }

        if (!$this->areAllCredentialsFilled($algoliaConfigTransfer)) {
            return $this->markConfigurationValuesAsInvalid(
                $configurationValueCollectionRequestTransfer,
                AlgoliaCredentialsConstraint::INVALID_SENTINEL,
                $this->algoliaConfig->getAlgoliaCredentialsKeys(),
            );
        }

        return $this->handleConfigurationValidation($configurationValueCollectionRequestTransfer, $algoliaConfigTransfer);
    }

    protected function normalizeDeletionKeysToEmptyValues(
        ConfigurationValueCollectionRequestTransfer $configurationValueCollectionRequestTransfer,
    ): ConfigurationValueCollectionRequestTransfer {
        $deletionKeys = $configurationValueCollectionRequestTransfer->getDeletionKeys();
        $normalizedIndices = [];

        $credentialKeys = $this->algoliaConfig->getAlgoliaCredentialsKeys();

        foreach ($configurationValueCollectionRequestTransfer->getDeletionKeys() as $index => $deletionKey) {
            if ($deletionKey->getSettingKey() === null || !in_array($deletionKey->getSettingKey(), $credentialKeys, true)) {
                continue;
            }

            $configurationValueCollectionRequestTransfer->addConfigurationValue(
                (new ConfigurationValueTransfer())
                    ->fromArray($deletionKey->toArray(), true)
                    ->setValue(static::DEFAULT_CREDENTIAL_VALUE),
            );

            $normalizedIndices[] = $index;
        }

        foreach ($normalizedIndices as $index) {
            unset($deletionKeys[$index]);
        }

        return $configurationValueCollectionRequestTransfer;
    }

    protected function hasCredentialFieldsInRequest(
        ConfigurationValueCollectionRequestTransfer $configurationValueCollectionRequestTransfer,
    ): bool {
        $credentialKeys = $this->algoliaConfig->getAlgoliaCredentialsKeys();

        foreach ($configurationValueCollectionRequestTransfer->getConfigurationValues() as $configurationValueTransfer) {
            if (in_array($configurationValueTransfer->getSettingKey(), $credentialKeys, true)) {
                return true;
            }
        }

        return false;
    }

    protected function buildAlgoliaConfigTransfer(
        ConfigurationValueCollectionRequestTransfer $configurationValueCollectionRequestTransfer,
    ): AlgoliaConfigTransfer {
        $changeConfigurationRequestValuesByKey = [];

        foreach ($configurationValueCollectionRequestTransfer->getConfigurationValues() as $configurationValueTransfer) {
            if ($configurationValueTransfer->getSettingKey() === null) {
                continue;
            }

            $changeConfigurationRequestValuesByKey[$configurationValueTransfer->getSettingKey()] = $configurationValueTransfer->getValue();
        }

        return (new AlgoliaConfigTransfer())
            ->setApplicationId(
                $changeConfigurationRequestValuesByKey[AlgoliaConfig::CONFIGURATION_KEY_APPLICATION_ID]
                    ?? $this->algoliaConfig->getApplicationId(),
            )
            ->setSearchOnlyApiKey(
                $changeConfigurationRequestValuesByKey[AlgoliaConfig::CONFIGURATION_KEY_SEARCH_ONLY_API_KEY]
                    ?? $this->algoliaConfig->getSearchOnlyApiKey(),
            )
            ->setAdminApiKey(
                $changeConfigurationRequestValuesByKey[AlgoliaConfig::CONFIGURATION_KEY_ADMIN_API_KEY]
                    ?? $this->algoliaConfig->getAdminApiKey(),
            );
    }

    protected function areAllCredentialsEmpty(AlgoliaConfigTransfer $algoliaConfigTransfer): bool
    {
        return empty($algoliaConfigTransfer->getApplicationId())
            && empty($algoliaConfigTransfer->getSearchOnlyApiKey())
            && empty($algoliaConfigTransfer->getAdminApiKey());
    }

    protected function areAllCredentialsFilled(AlgoliaConfigTransfer $algoliaConfigTransfer): bool
    {
        return !empty($algoliaConfigTransfer->getApplicationId())
            && !empty($algoliaConfigTransfer->getSearchOnlyApiKey())
            && !empty($algoliaConfigTransfer->getAdminApiKey());
    }

    protected function isAlgoliaSearchProviderEnabled(): bool
    {
        return $this->algoliaConfig->isSearchInFrontendEnabledForProducts()
            || $this->algoliaConfig->isSearchInFrontendEnabledForCmsPages();
    }

    protected function handleConfigurationValidation(
        ConfigurationValueCollectionRequestTransfer $configurationValueCollectionRequestTransfer,
        AlgoliaConfigTransfer $algoliaConfigTransfer,
    ): ConfigurationValueCollectionRequestTransfer {
        $algoliaApiCredentialsValidationTransfer = $this->apiCredentialsValidator->validate($algoliaConfigTransfer);

        $validationResultIndexedByKey = [
            AlgoliaConfig::CONFIGURATION_KEY_APPLICATION_ID => $algoliaApiCredentialsValidationTransfer->getIsAccountIdValid(),
            AlgoliaConfig::CONFIGURATION_KEY_SEARCH_ONLY_API_KEY => $algoliaApiCredentialsValidationTransfer->getIsSearchOnlyApiKeyValid(),
            AlgoliaConfig::CONFIGURATION_KEY_ADMIN_API_KEY => $algoliaApiCredentialsValidationTransfer->getIsAdminApiKeyValid(),
        ];

        $invalidKeys = array_keys(array_filter($validationResultIndexedByKey, fn (?bool $isValid) => $isValid === false));

        return $this->markConfigurationValuesAsInvalid(
            $configurationValueCollectionRequestTransfer,
            AlgoliaCredentialsConstraint::INVALID_SENTINEL,
            $invalidKeys,
        );
    }

    /**
     * @param array<string> $invalidKeys
     */
    protected function markConfigurationValuesAsInvalid(
        ConfigurationValueCollectionRequestTransfer $configurationValueCollectionRequestTransfer,
        string $sentinel,
        array $invalidKeys
    ): ConfigurationValueCollectionRequestTransfer {
        foreach ($configurationValueCollectionRequestTransfer->getConfigurationValues() as $configurationValueTransfer) {
            if (in_array($configurationValueTransfer->getSettingKey(), $invalidKeys, true)) {
                $configurationValueTransfer->setValue($sentinel);
            }
        }

        return $configurationValueCollectionRequestTransfer;
    }
}
