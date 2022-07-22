# m2-blueacorn-core
BlueAcorn Core Module for Magento 2

## Installation

```bash
composer config repositories.blueacorninc/m2-blueacorn-core git git@github.com:blueacorninc/m2-blueacorn-core.git
composer require blueacorn/module-blueacorn-core
bin/magento setup:upgrade && bin/magento cache:flush
```

## Creating Data Patches

1. Create `Vendor/Module/Setup/Patch/Data/PatchFile.php`
2. Create `*.html` files in `Vendor/Module/Setup/resources/patches/*.html`

`Vendor.Module/Setup/Patch/Data/PatchFile.php`
```php
<?php
namespace Vendor\Module\Setup\Patch\Data;

use BlueAcorn\Core\Setup\AbstractDataPatch;

class Test extends AbstractDataPatch
{
    /** Should match the modules name */
    public const MODULE_KEY = 'Vendor_Module';

    /**
     * Should return an array of blocks compatible with
     * \BlueAcorn\Core\Helper\Installs
     */
    protected function getBlocks(): array
    {
        return [
            [
                'title' => 'test 1',
                'identifier' => 'ba-test-1'
            ]
        ];
    }

    /**
     * Should return an array of pages compatible with
     * \BlueAcorn\Core\Helper\Installs
     */
    protected function getPages(): array
    {
        return [
            [
                'title' => 'Test Page',
                'identifier' => 'ba-test-page-1',
                'page_layout' => '1column',
                'content_heading' => ''
            ]
        ];
    }
}
```