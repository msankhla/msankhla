<?php
/**
 * FedEx Core component
 *
 * @category    FedEx
 * @package     FedEx_Core
 * @author      Andrey Rapshtynsky <andrey.bh@gmail.com>
 * @copyright   FedEx (https://crossborder.fedex.com/)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace FedEx\Core\Model;

class Tools
{
    /**
     * Checks if directory exist.
     *
     * @param string $path
     * @param bool $create
     * @return bool
     */
    public static function checkDir($path, $create = true)
    {
        if ($create && !is_dir($path)) {
            mkdir($path, 0777, true);
        }

        return is_dir($path);
    }

    /**
     * Returns UUID
     *
     * @return string
     * @throws \Exception
     */
    public static function uuid(): string
    {
        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
