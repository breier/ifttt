<?php

/**
 * PHP Version 7
 *
 * Dynamic DNS Controller File
 *
 * @category Controller
 * @package  SmartAPI\Controller
 * @author   Andre Breier <breier.de@gmail.com>
 * @license  GPLv3 /LICENSE
 */

namespace SmartAPI\Controller;

use Breier\ExtendedArray\ExtendedArray;
use Symfony\Component\HttpFoundation\{Request, Response};
use SmartAPI\Exception\{HostException, RequestException};
use SmartAPI\Model\Hosts;

/**
 * Dynamic DNS Controller class
 */
class DynamicDNS extends BaseController
{
    private $hosts;

    /**
     * Instantiate Hosts
     */
    public function __construct()
    {
        $this->hosts = new Hosts();
    }

    /**
     * @route POST /ddns
     */
    public function update(Request $request): Response
    {
        try {
            $this->validateMacAddress($this->getRequestData($request));
        } catch (RequestException $e) {
            return $this->createResponse($e->getMessage(), 400);
        }

        $hostInfo = $this->hosts->find(
            $this->getRequestData($request)->offsetGet(
                Hosts::REQUEST_OBJECT_KEY_MAC_ADDRESS
            )
        );

        $hostInfo->ipAddress = $request->getClientIp();

        try {
            $this->hosts->update($hostInfo);
        } catch (HostException $e) {
            return $this->createResponse($e->getMessage(), 422);
        }

        return $this->createResponse(
            [
                "macAddress" => $hostInfo->macAddress,
                "ipAddress" => $hostInfo->ipAddress,
            ]
        );
    }

    /**
     * Validate MAC Address from request
     *
     * @throws RequestException
     */
    private function validateMacAddress(ExtendedArray $requestData): void
    {
        if (!$requestData->offsetExists(Hosts::REQUEST_OBJECT_KEY_MAC_ADDRESS)) {
            throw new RequestException(
                Hosts::REQUEST_OBJECT_KEY_MAC_ADDRESS . ' object key is missing!'
            );
        }

        $macAddress = $requestData->offsetGet(Hosts::REQUEST_OBJECT_KEY_MAC_ADDRESS);
        if (preg_match('/^([0-9a-fA-F]{2}[\:\-]?){5}[0-9a-fA-F]{2}$/', $macAddress) !== 1) {
            throw new RequestException(
                Hosts::REQUEST_OBJECT_KEY_MAC_ADDRESS . ' has invalid format!'
            );
        }

        if ($this->hosts->find($macAddress)->count() === 0) {
            throw new RequestException(
                Hosts::REQUEST_OBJECT_KEY_MAC_ADDRESS . ' not found!'
            );
        }
    }
}
