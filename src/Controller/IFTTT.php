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
use SmartAPI\Traits\IFTTTaware;

/**
 * IFTTT Controller class
 */
class IFTTT extends BaseController
{
    use IFTTTaware;

    /**
     * @route POST /ifttt/v1/test/setup
     */
    public function setup(Request $request): Response
    {
        try {
            $this->IFTTTvalidateRequest($request);
        } catch (RequestException $e) {
            return $this->IFTTTresponse($e->getMessage(), 401);
        }

        $scaffold['data']['samples']['actions'] = [
            "wol_pcd" => [],
        ];

        return $this->createResponse($scaffold);
    }

    /**
     * @route GET /ifttt/v1/status
     */
    public function status(Request $request): Response
    {
        try {
            $this->IFTTTvalidateRequest($request);
        } catch (RequestException $e) {
            return $this->IFTTTresponse($e->getMessage(), 401);
        }

        return $this->createResponse('OK');
    }
}
