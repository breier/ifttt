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

use Symfony\Component\HttpFoundation\{Request, Response};
use SmartAPI\Exception\{HostException, RequestException};
use SmartAPI\Model\{Hosts, HostInfo};
use SmartAPI\Traits\Validator;

/**
 * Dynamic DNS Controller class
 */
class DynamicDNS extends BaseController
{
    use Validator;

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
    public function create(Request $request): Response
    {
        $requestData = $this->getRequestData($request);

        try {
            $this->validateMacAddress(
                $requestData,
                Hosts::REQUEST_OBJECT_KEY_MAC_ADDRESS
            );

            throw new RequestException(
                'Invalid ' . Hosts::REQUEST_OBJECT_KEY_MAC_ADDRESS,
                422
            );
        } catch (RequestException $e) {
            if ($e->getCode() !== 404) {
                return $this->createResponse($e->getMessage(), $e->getCode());
            }
        }

        $hostInfo = new HostInfo($requestData);

        $hostInfo->ipAddress = $request->getClientIp();

        try {
            $this->hosts->create($hostInfo);
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
     * @route PUT /ddns
     */
    public function update(Request $request): Response
    {
        try {
            $this->validateMacAddress(
                $this->getRequestData($request),
                Hosts::REQUEST_OBJECT_KEY_MAC_ADDRESS
            );
        } catch (RequestException $e) {
            return $this->createResponse($e->getMessage(), $e->getCode());
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
}
