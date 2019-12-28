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

use Symfony\Component\HttpFoundation\Response;

/**
 * IFTTT Controller class
 */
class IFTTT extends BaseController
{
    /**
     * @route POST /ifttt/v1/test/setup
     */
    public function setup(): Response
    {
        $scaffold['data']['samples']['actions'] = [
            "wol_pcd" => [],
        ];

        return $this->createResponse($scaffold);
    }

    /**
     * @route GET /ifttt/v1/status
     */
    public function status(): Response
    {
        return $this->createResponse('OK');
    }
}
