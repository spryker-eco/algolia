<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types = 1);

namespace SprykerEco\Zed\Algolia\Business\Mapper;

use ArrayObject;
use Generated\Shared\Transfer\CmsPageAttributesTransfer;
use Generated\Shared\Transfer\CmsPageMetaAttributesTransfer;
use Generated\Shared\Transfer\CmsPagePublishedTransfer;
use Generated\Shared\Transfer\CmsPageTransfer;
use SprykerEco\Shared\Algolia\Enum\AlgoliaCmsPageObjectEnum;

class CmsPageMapper implements CmsPageMapperInterface
{
    /**
     * @var string
     */
    protected const KEY_PLACEHOLDERS = 'placeholders';

    /**
     * @var string
     */
    protected const KEY_TITLE = 'title';

    /**
     * @var string
     */
    protected const KEY_CONTENT = 'content';

    /**
     * Maximum size in bytes for text content in Algolia cms page.
     *
     * @var int
     */
    protected const MAX_TEXT_SIZE_IN_BYTES = 90 * 1024; // 90KB

    /**
     * @param array $flattenedLocaleCmsPageData
     *
     * @return array|null
     */
    public function mapCmsPageDataToAlgoliaData(
        CmsPagePublishedTransfer $cmsPagePublishedTransfer,
        string $locale,
        CmsPageTransfer $cmsPageTransfer,
        array $flattenedLocaleCmsPageData
    ): ?array {
        $cmsPageAttributesTransfer = $this->getLocatePagAttribute($locale, $cmsPageTransfer->getPageAttributes());
        $metaAttributes = $this->getLocaleMetaAttributes($locale, $cmsPageTransfer->getMetaAttributes());
        $url = $cmsPageAttributesTransfer->getUrlOrFail();

        $title = $flattenedLocaleCmsPageData[static::KEY_PLACEHOLDERS][static::KEY_TITLE] ?? '';
        $content = $flattenedLocaleCmsPageData[static::KEY_PLACEHOLDERS][static::KEY_CONTENT] ?? '';

        return [
            AlgoliaCmsPageObjectEnum::OBJECT_ID->value => $cmsPagePublishedTransfer->getId(),
            AlgoliaCmsPageObjectEnum::ID_CMS_PAGE->value => $cmsPagePublishedTransfer->getId(),
            AlgoliaCmsPageObjectEnum::STORE->value => array_map(
                fn ($store) => $store->getName(),
                $cmsPageTransfer->getStoreRelation()->getStores()->getArrayCopy(),
            ),
            AlgoliaCmsPageObjectEnum::LOCALE->value => $locale,
            AlgoliaCmsPageObjectEnum::IS_ACTIVE->value => $cmsPageTransfer->getIsActive(),
            AlgoliaCmsPageObjectEnum::URL->value => $url,
            AlgoliaCmsPageObjectEnum::NAME->value => $this->sanitizeHtmlToText($cmsPageAttributesTransfer->getName() ?? ''),
            AlgoliaCmsPageObjectEnum::TITLE->value => $this->sanitizeHtmlToText($this->truncateText($title)),
            AlgoliaCmsPageObjectEnum::CONTENT->value => $this->sanitizeHtmlToText($this->truncateText($content)),
            AlgoliaCmsPageObjectEnum::META_TITLE->value => $metaAttributes->getMetaTitle(),
            AlgoliaCmsPageObjectEnum::META_DESCRIPTION->value => $metaAttributes->getMetaDescription(),
            AlgoliaCmsPageObjectEnum::META_KEYWORDS->value => $metaAttributes->getMetaKeywords(),
            AlgoliaCmsPageObjectEnum::LAST_UPDATED->value => strtotime($cmsPagePublishedTransfer->getUpdatedAt()),
            AlgoliaCmsPageObjectEnum::CREATED->value => strtotime($cmsPagePublishedTransfer->getCreatedAt()),
            AlgoliaCmsPageObjectEnum::VALID_FROM->value => $this->formatValidityDate($cmsPageTransfer->getValidFrom()),
            AlgoliaCmsPageObjectEnum::VALID_TO->value => $this->formatValidityDate($cmsPageTransfer->getValidTo()),
        ];
    }

    protected function getLocatePagAttribute(string $locale, ArrayObject $cmsPageAttributesTransfers): CmsPageAttributesTransfer
    {
        foreach ($cmsPageAttributesTransfers as $cmsPageAttributesTransfer) {
            if ($cmsPageAttributesTransfer->getLocaleName() === $locale) {
                return $cmsPageAttributesTransfer;
            }
        }

        return new CmsPageAttributesTransfer();
    }

    protected function getLocaleMetaAttributes(string $locale, ArrayObject $metaAttributes): CmsPageMetaAttributesTransfer
    {
        foreach ($metaAttributes as $metaAttribute) {
            if ($metaAttribute->getLocaleName() === $locale) {
                return $metaAttribute;
            }
        }

        return new CmsPageMetaAttributesTransfer();
    }

    protected function sanitizeHtmlToText(string $html): string
    {
        // Remove Twig tags: {{ }}, {% %}, and {# #}
        $html = preg_replace('/\{\{.*?\}\}|\{%.*?%\}|\{#.*?#\}/s', ' ', $html);

        $html = preg_replace(
            '/<(\/?(p|br|div|li|ul|ol|tr|th|td|h[1-6]|blockquote|section|article|header|footer|hr|figcaption|label|option))[^>]*>/i',
            ' ',
            $html,
        );
        $text = strip_tags($html);
        $text = preg_replace('/\s+/', ' ', $text);

        return trim($text);
    }

    /**
     * Truncates text if it exceeds the maximum.
     *
     * @return string|null
     */
    protected function truncateText(string $text): ?string
    {
        if ($text === '') {
            return $text;
        }

        $textSize = strlen($text);

        if ($textSize <= static::MAX_TEXT_SIZE_IN_BYTES) {
            return $text;
        }

        $truncatedText = substr($text, 0, static::MAX_TEXT_SIZE_IN_BYTES - 100);

        return $truncatedText . '... [content truncated due to size limit]';
    }

    /**
     * @param string|null $validityDate
     */
    protected function formatValidityDate(?string $validityDate): int
    {
        if (!$validityDate) {
            return 0;
        }

        return strtotime($validityDate) ?: 0;
    }
}
