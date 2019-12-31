<?php

/**
 * PHP Version 7
 *
 * IFTTT Controller File
 *
 * @category Controller
 * @package  SmartAPI\Controller
 * @author   Andre Breier <breier.de@gmail.com>
 * @license  GPLv3 /LICENSE
 */

namespace SmartAPI\Controller;

use SmartAPI\Exception\RequestException;
use Symfony\Component\HttpFoundation\{Request, Response};
use SmartAPI\Traits\IFTTTvalidator;

/**
 * IFTTT Controller class
 */
class IFTTT extends BaseController
{
    use IFTTTvalidator;

    /**
     * @route POST /ifttt/v1/test/setup
     */
    public function setup(Request $request): Response
    {
        try {
            $this->validateRequestHeaders($request);
        } catch (RequestException $e) {
            return $this->getIFTTT()->createResponse(
                $e->getMessage(),
                $e->getCode()
            );
        }

        $scaffold['data']['samples']['actions'] = [
            "wol_pcd" => [
                "mac_address" => '01-23-45-AB-CD-EF',
                "password" => 'Local Host or Router',
            ],
            "suspend" => [
                "mac_address" => '01-23-45-AB-CD-EF',
                "password" => 'Host to be suspended',
            ],
        ];

        return $this->createResponse($scaffold);
    }

    /**
     * @route GET /ifttt/v1/status
     */
    public function status(Request $request): Response
    {
        try {
            $this->validateRequestHeaders($request);
        } catch (RequestException $e) {
            return $this->getIFTTT()->createResponse(
                $e->getMessage(),
                $e->getCode()
            );
        }

        return $this->createResponse('OK');
    }
}
