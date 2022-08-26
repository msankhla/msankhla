/**
 * FedEx Cross Border component
 *
 * @category    FedEx
 * @package     FedEx_CrossBorder
 * @author      Andrey Rapshtynsky <andrey.bh@gmail.com>
 * @copyright   FedEx (https://crossborder.fedex.com/)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

var config = {
    config: {
        mixins: {
            'Magento_Bundle/js/product-summary': {
                'FedEx_CrossBorder/js/bundle/product-summary-mixin': true
            },

            'Magento_GroupedProduct/js/product-ids-resolver': {
                'FedEx_CrossBorder/js/grouped/product-ids-resolver-mixin': true
            },

            'Magento_Swatches/js/swatch-renderer': {
                'FedEx_CrossBorder/js/swatch-renderer-mixin': true
            }
        }
    }
};
