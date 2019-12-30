<?php

/**
 * PHP Version 7
 *
 * IFTTT Model
 *
 * @category Model
 * @package  SmartAPI\Model
 * @author   Andre Breier <breier.de@gmail.com>
 * @license  GPLv3 /LICENSE
 */

namespace SmartAPI\Model;

use Symfony\Component\HttpFoundation\{Request, Response};
use Breier\ExtendedArray\ExtendedArray;

/**
 * IFTTT Model class
 */
class IFTTT
{
    public const ACTION_FIELD_MAC_ADDRESS = 'mac_address';
    public const ACTION_FIELD_PASSWORD = 'password';
    public const SERVICE_KEY_HEADER = 'ifttt-service-key';
    public const CHANNEL_KEY_HEADER = 'ifttt-channel-key';
    public const TEST_MODE_HEADER = 'ifttt-test-mode';

    private $headers;
    private $serviceKey;

    /**
     * Instantiate variables
     */
    public function __construct(?Request $request = null)
    {
        if ($request !== null) {
            $this->getHeaders($request);
        }

        $this->getServiceKey();
    }

    /**
     * Get IFTTT Headers
     */
    public function getHeaders(Request $request): ExtendedArray
    {
        if ($this->headers instanceof ExtendedArray) {
            return $this->headers;
        }

        $IFTTTcallback = function ($key) {
            return preg_match('/^(IFTTT|ifttt)-.*/', $key) === 1;
        };

        $flattenHeaders = function ($item) {
            if ($item instanceof ExtendedArray) {
                return $item->first()->element();
            }
        };

        $this->headers = (new ExtendedArray($request->headers->getIterator()))
            ->filter($IFTTTcallback, ARRAY_FILTER_USE_KEY)
            ->mapWithObjects($flattenHeaders);

        return $this->headers;
    }

    /**
     * Get IFTTT Service Key
     */
    public function getServiceKey(): ?string
    {
        if (null !== $this->serviceKey) {
            return $this->serviceKey;
        }

        $this->serviceKey = $_ENV['IFTTT_SERVICE_KEY'] ?? null;

        return $this->serviceKey;
    }

    /**
     * IFTTT request Is Test Mode
     */
    public function isTestMode(Request $request): bool
    {
        $requestHeaders = $this->getHeaders($request);

        if ($requestHeaders->offsetExists(self::TEST_MODE_HEADER)) {
            return !! $this->getHeaders($request)->offsetGet(
                self::TEST_MODE_HEADER
            );
        }

        return false;
    }

    /**
     * Create a Symfony HTTP Response Object
     *
     * @param mixed $mixedContent To add to the response
     *
     * @throws ResponseException
     */
    public function createResponse($mixedContent, int $httpCode = 200): Response
    {
        $response = ($httpCode < 300)
            ? ['data' => [['id' => $mixedContent]]]
            : ['errors' => [['message' => $mixedContent]]];

        return new Response(
            json_encode($response),
            $httpCode,
            ['Content-Type' => 'application/json']
        );
    }
}
