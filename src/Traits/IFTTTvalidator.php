<?php

/**
 * PHP Version 7
 *
 * IFTTT Validator Trait File
 *
 * @category Traits
 * @package  SmartAPI\Traits
 * @author   Andre Breier <breier.de@gmail.com>
 * @license  GPLv3 /LICENSE
 */

namespace SmartAPI\Traits;

use Symfony\Component\HttpFoundation\Request;
use Breier\ExtendedArray\ExtendedArray;
use SmartAPI\Exception\RequestException;
use SmartAPI\Model\IFTTT;

/**
 * IFTTT Validator Trait class
 */
trait IFTTTvalidator
{
    private $ifttt;

    /**
     * Get IFTTT Model
     */
    protected function getIFTTT()
    {
        if ($this->ifttt instanceof IFTTT) {
            return $this->ifttt;
        }

        $this->ifttt = new IFTTT();

        return $this->ifttt;
    }

    /**
     * Validate Request IFTTT headers
     *
     * @throws RequestException
     */
    protected function validateRequestHeaders(Request $request): void
    {
        $requestHeaders = $this->getIFTTT()->getHeaders($request);

        if ($requestHeaders->offsetExists(IFTTT::SERVICE_KEY_HEADER)) {
            $requestServiceKey = $requestHeaders->offsetGet(
                IFTTT::SERVICE_KEY_HEADER
            );
        } elseif ($requestHeaders->offsetExists(IFTTT::CHANNEL_KEY_HEADER)) {
            $requestServiceKey = $requestHeaders->offsetGet(
                IFTTT::CHANNEL_KEY_HEADER
            );
        } else {
            $requestServiceKey = 'INVALID';
        }

        if ($this->getIFTTT()->getServiceKey() !== $requestServiceKey) {
            throw new RequestException('Invalid IFTTT-Channel-Key', 401);
        }
    }

    /**
     * Validate Request IFTTT
     *
     * @throws RequestException
     */
    protected function validateRequest(ExtendedArray $requestData): void
    {
        if (!$requestData->offsetExists('actionFields')) {
            throw new RequestException('Invalid IFTTT request content!');
        }

        if (
            !$requestData->actionFields->offsetExists(IFTTT::ACTION_FIELD_MAC_ADDRESS)
            || !$requestData->actionFields->offsetExists(IFTTT::ACTION_FIELD_PASSWORD)
        ) {
            throw new RequestException('Invalid IFTTT request body!');
        }
    }
}
