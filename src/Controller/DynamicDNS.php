<?php

/**
 * PHP Version 7
 *
 * Dynamic DNS Controller File
 *
 * @category Controller
 * @package  Breier\Controller
 * @author   Andre Breier <breier.de@gmail.com>
 * @license  GPLv3 /LICENSE
 */

namespace SmartAPI\Controller;

use Breier\ExtendedArray\ExtendedArray;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Dynamic DNS Controller class
 */
class DynamicDNS extends BaseController
{
    public function __construct()
    {
        if (false) {
            error_log("session is running");
        }
    }

    public function update(Request $request): Response
    {
        $requestData = ExtendedArray::fromJSON($request->getContent());
        $requestData->ipAddress = $request->getClientIp();
        return $this->createResponse($requestData);
    }
}
