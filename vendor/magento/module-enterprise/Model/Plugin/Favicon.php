<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Enterprise\Model\Plugin;

use Magento\Theme\Model\Favicon\Favicon as DefaultFavicon;

/**
 * Store switcher block plugin
 */
class Favicon
{
    /**
     * Return enterprise favicon
     *
     * @param DefaultFavicon $subject
     * @return string
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetDefaultFavicon(DefaultFavicon $subject)
    {
        return 'Magento_Enterprise::favicon.ico';
    }
}
