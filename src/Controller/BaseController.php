<?php

/**
 * PHP Version 7
 *
 * Base Controller File
 *
 * @category Controller
 * @package  SmartAPI\Controller
 * @author   Andre Breier <breier.de@gmail.com>
 * @license  GPLv3 /LICENSE
 */

namespace SmartAPI\Controller;

use Breier\ExtendedArray\ExtendedArray;
use SmartAPI\Exception\{RequestException, ResponseException};
use Symfony\Component\HttpFoundation\{Request, Response};

/**
 * Base Controller class
 */
class BaseController
{
    private $requestData;

    public function __construct()
    {
        if (false) {
            error_log("session is running");
        }
    }

    /**
     * @route GET /
     */
    public function index(): Response
    {
        return $this->createResponse("Breier Services for SmartAPI");
    }

    /**
     * Get Request Data
     */
    public function getRequestData(Request $request): ExtendedArray
    {
        if (null !== $this->requestData) {
            return $this->requestData;
        }

        $content = (string) $request->getContent();
        if (empty($content)) {
            throw new RequestException("Invalid Request!");
        }

        $this->requestData = ExtendedArray::fromJSON($content);
        return $this->requestData;
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
