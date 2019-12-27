<?php

/**
 * PHP Version 7
 *
 * Host Controller File
 *
 * @category Controller
 * @package  SmartAPI\Controller
 * @author   Andre Breier <breier.de@gmail.com>
 * @license  GPLv3 /LICENSE
 */

namespace SmartAPI\Controller;

use Breier\ExtendedArray\ExtendedArray;
use SmartAPI\Exception\RequestException;
use SmartAPI\Model\Hosts;

/**
 * Host Controller class
 */
abstract class HostController extends BaseController
{
    protected const REQUEST_OBJECT_KEY_MAC_ADDRESS = 'macAddress';

    private $hosts;

    /**
     * Instantiate Hosts
     */
    public function __construct()
    {
        $this->hosts = new Hosts();
    }

    /**
     * Get Hosts
     */
    protected function getHosts(): Hosts
    {
        return $this->hosts;
    }

    /**
     * Validate MAC Address from request
     *
     * @throws RequestException
     */
    protected function validateMacAddress(ExtendedArray $requestData): void
    {
        if (!$requestData->offsetExists(self::REQUEST_OBJECT_KEY_MAC_ADDRESS)) {
            throw new RequestException(
                self::REQUEST_OBJECT_KEY_MAC_ADDRESS . ' object key is missing!'
            );
        }

        $macAddress = $requestData->offsetGet(self::REQUEST_OBJECT_KEY_MAC_ADDRESS);
        if (preg_match('/^([0-9a-fA-F]{2}[\:\-]?){5}[0-9a-fA-F]{2}$/', $macAddress) !== 1) {
            throw new RequestException(
                self::REQUEST_OBJECT_KEY_MAC_ADDRESS . ' has invalid format!'
            );
        }

        if (empty($this->hosts->find($macAddress))) {
            throw new RequestException(
                self::REQUEST_OBJECT_KEY_MAC_ADDRESS . ' not found!'
            );
        }
    }
}
