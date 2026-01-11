<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerEco\Shared\Algolia\Enum;

enum AlgoliaCmsPageObjectEnum: string
{
    case OBJECT_ID = 'objectID';
    case ID_CMS_PAGE = 'id_cms_page';
    case STORE = 'store';
    case LOCALE = 'locale';
    case TYPE = 'type';
    case IS_ACTIVE = 'is-active';
    case URL = 'url';
    case TITLE = 'title';
    case CONTENT = 'content';
    case META_TITLE = 'metaTitle';
    case META_DESCRIPTION = 'metaDescription';
    case META_KEYWORDS = 'metaKeywords';
    case LAST_UPDATED = 'lastUpdated';
    case CREATED = 'created';
    case NAME = 'name';
    case VALID_FROM = 'valid_from';
    case VALID_TO = 'valid_to';
}
