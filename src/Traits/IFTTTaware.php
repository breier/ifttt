<?php

/**
 * PHP Version 7
 *
 * IFTTT Trait File
 *
 * @category Traits
 * @package  SmartAPI\Traits
 * @author   Andre Breier <breier.de@gmail.com>
 * @license  GPLv3 /LICENSE
 */

namespace SmartAPI\Traits;

use Breier\ExtendedArray\ExtendedArray;
use SmartAPI\Exception\RequestException;
use Symfony\Component\HttpFoundation\{Request, Response};

/**
 * IFTTT Trait class
 */
trait IFTTTaware
{
    private $headers;
    private $serviceKey;

    /**
     * Get IFTTT Headers
     */
    public function IFTTTgetHeaders(Request $request): ExtendedArray
    {
        if ($this->headers instanceof ExtendedArray) {
            return $this->headers;
        }

        $IFTTTcallback = function ($key) {
            return preg_match('/^ifttt-.*/', $key) === 1;
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
    public function IFTTTgetServiceKey(): ?string
    {
        if (null !== $this->serviceKey) {
            return $this->serviceKey;
        }

        $this->serviceKey = $_ENV['IFTTT_SERVICE_KEY'] ?? null;

        return $this->serviceKey;
    }

    /**
     * Validate Request IFTTT headers
     */
    public function IFTTTvalidateRequest(Request $request): void
    {
        $requestHeaders = $this->IFTTTgetHeaders($request);

        if ($requestHeaders->offsetExists('ifttt-service-key')) {
            $requestServiceKey = $requestHeaders->offsetGet('ifttt-service-key');
        } elseif ($requestHeaders->offsetExists('ifttt-channel-key')) {
            $requestServiceKey = $requestHeaders->offsetGet('ifttt-channel-key');
        } else {
            $requestServiceKey = 'INVALID';
        }

        if ($this->IFTTTgetServiceKey() !== $requestServiceKey) {
            throw new RequestException('Invalid IFTTT-Channel-Key');
        }
    }

    /**
     * IFTTT request Is Test Mode
     */
    public function IFTTTisTestMode(Request $request): bool
    {
        return !! $this->IFTTTgetHeaders($request)->offsetGet('ifttt-test-mode') ?? false;
    }

    /**
     * Create a Symfony HTTP Response Object
     *
     * @param mixed $mixedContent To add to the response
     *
     * @throws ResponseException
     */
    protected function IFTTTresponse($mixedContent, int $httpCode = 200): Response
    {
        $response = ($httpCode < 300)
            ? ['data' => [['id' => $mixedContent]]]
            : ['errors' => [['message' => $mixedContent]]];

        return new Response(
            \json_encode($response),
            $httpCode,
            ['Content-Type' => 'application/json']
        );
    }
}
