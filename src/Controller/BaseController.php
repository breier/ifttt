<?php

/**
 * PHP Version 7
 *
 * Base Controller File
 *
 * @category Controller
 * @package  Breier\Controller
 * @author   Andre Breier <breier.de@gmail.com>
 * @license  GPLv3 /LICENSE
 */

namespace SmartAPI\Controller;

use Symfony\Component\HttpFoundation\Response;
use Breier\ExtendedArray\ExtendedArray;
use SmartAPI\Exception\ResponseException;

/**
 * Base Controller class
 */
class BaseController
{
    public function __construct()
    {
        if (false) {
            error_log("session is running");
        }
    }

    public function index(): Response
    {
        return $this->createResponse("Breier Services for SmartAPI");
    }

    /**
     * Create a Symfony HTTP Response Object
     *
     * @param mixed $mixedContent To add to the response
     *
     * @throws ResponseException
     */
    protected function createResponse($mixedContent, int $httpCode = 200): Response
    {
        if (\is_string($mixedContent)) {
            $mixedContent = new ExtendedArray(["message" => $mixedContent]);
        }

        if (!$mixedContent instanceof ExtendedArray) {
            if (!ExtendedArray::isArray($mixedContent)) {
                throw new ResponseException("Invalid content object!");
            }

            $mixedContent = new ExtendedArray($mixedContent);
        }

        return new Response(
            $mixedContent->jsonSerialize(),
            $httpCode,
            ['Content-Type' => 'application/json']
        );
    }
}
