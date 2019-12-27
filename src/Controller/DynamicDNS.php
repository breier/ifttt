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

/**
 * Dynamic DNS Controller class
 */
class DynamicDNS extends HostController
{
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

        $hostInfo = $this->getHosts()->find(
            $this->getRequestData($request)->offsetGet(
                self::REQUEST_OBJECT_KEY_MAC_ADDRESS
            )
        );

        $hostInfo->ipAddress = $request->getClientIp();

        try {
            $this->getHosts()->update($hostInfo);
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
}
