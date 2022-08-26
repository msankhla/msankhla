<?php
/**
 * FedEx Cross Border component
 *
 * @category    FedEx
 * @package     FedEx_CrossBorder
 * @author      Andrey Rapshtynsky <andrey.bh@gmail.com>
 * @copyright   FedEx (https://crossborder.fedex.com/)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace FedEx\CrossBorder\Api\Data\OrderManagement\ReadyForExport;

interface DocumentInterface
{
    const ID_DOC                    = 'id_doc';
    const DOC_NAME                  = 'doc_name';
    const URL                       = 'url';
    const FORMAT                    = 'format';
    const REQUIRES_PHYSICAL_COPY    = 'requires_physical_copy';

    /**
     * Returns id
     *
     * @return string
     */
    public function getIdDoc();

    /**
     * Sets id
     *
     * @param string $value
     * @return DocumentInterface
     */
    public function setIdDoc($value);

    /**
     * Returns name
     *
     * @return string
     */
    public function getDocName();

    /**
     * Sets name
     *
     * @param string $value
     * @return DocumentInterface
     */
    public function setDocName($value);

    /**
     * Returns url
     *
     * @return string
     */
    public function getUrl();

    /**
     * Sets url
     *
     * @param string $value
     * @return DocumentInterface
     */
    public function setUrl($value);

    /**
     * Returns format
     *
     * @return string
     */
    public function getFormat();

    /**
     * Sets format
     *
     * @param string $value
     * @return DocumentInterface
     */
    public function setFormat($value);

    /**
     * Returns requires_physical_copy flag value
     *
     * @return bool
     */
    public function getRequiresPhysicalCopy();

    /**
     * Sets requires_physical_copy flag value
     *
     * @param bool $value
     * @return DocumentInterface
     */
    public function setRequiresPhysicalCopy($value);
}