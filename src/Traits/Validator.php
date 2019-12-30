<?php

/**
 * PHP Version 7
 *
 * Validator Trait File
 *
 * @category Traits
 * @package  SmartAPI\Traits
 * @author   Andre Breier <breier.de@gmail.com>
 * @license  GPLv3 /LICENSE
 */

namespace SmartAPI\Traits;

use Breier\ExtendedArray\ExtendedArray;
use SmartAPI\Exception\RequestException;

/**
 * Validator Trait Class
 */
trait Validator
{
    /**
     * Validate MAC Address from request
     *
     * @throws RequestException
     */
    private function validateMacAddress(
        ExtendedArray $requestData,
        string $macAddressKeyName
    ): void {
        if (!$requestData->offsetExists($macAddressKeyName)) {
            throw new RequestException(
                "{$macAddressKeyName} object key is missing!"
            );
        }

        $macAddress = $requestData->offsetGet($macAddressKeyName);
        if (preg_match('/^([0-9a-fA-F]{2}[\:\-]?){5}[0-9a-fA-F]{2}$/', $macAddress) !== 1) {
            throw new RequestException(
                "{$macAddressKeyName} has invalid format!"
            );
        }

        if ($this->hosts->find($macAddress)->count() === 0) {
            throw new RequestException(
                "{$macAddressKeyName} not found!",
                404
            );
        }
    }
}