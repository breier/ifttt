<?php

/**
 * PHP Version 7
 *
 * Actions Controller File
 *
 * @category Controller
 * @package  SmartAPI\Controller
 * @author   Andre Breier <breier.de@gmail.com>
 * @license  GPLv3 /LICENSE
 */

namespace SmartAPI\Controller;

use phpseclib\Net\SSH2;
use Breier\ExtendedArray\ExtendedArray;
use Symfony\Component\HttpFoundation\{Request, Response};
use SmartAPI\Exception\{RequestException, HostException};
use SmartAPI\Traits\{IFTTTvalidator, Validator};
use SmartAPI\Model\Hosts;
use ErrorException;

/**
 * Actions Controller class
 */
class Actions extends BaseController
{
    use IFTTTvalidator;
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
     * @route POST /ifttt/v1/actions/wol_pcd
     */
    public function wakeOnLan(Request $request): Response
    {
        $actionName = basename($request->getPathInfo());

        return $this->sshAction(
            $request,
            "/system script run {$actionName}"
        );
    }

    /**
     * IFTTT prepare to execute given ssh action
     */
    private function sshAction(Request $request, string $command): Response
    {
        try {
            $this->validateRequestHeaders($request);
            $this->validateRequest($this->getRequestData($request));
            $this->validateMacAddress(
                $this->getRequestData($request)->actionFields,
                $this->getIFTTT()::ACTION_FIELD_MAC_ADDRESS
            );
        } catch (RequestException $e) {
            return $this->getIFTTT()->createResponse(
                $e->getMessage(),
                $e->getCode()
            );
        }

        if ($this->getIFTTT()->isTestMode($request)) {
            return $this->getIFTTT()->createResponse('1q0o2w9i3e8u4r7y5t');
        }

        try {
            $hostInfo = $this->hosts->getFullHostInfo(
                $this->getRequestData($request)->actionFields->offsetGet(
                    $this->getIFTTT()::ACTION_FIELD_MAC_ADDRESS
                )
            );

            $hostInfo->password = $this
                ->getRequestData($request)
                ->actionFields
                ->offsetGet(
                    $this->getIFTTT()::ACTION_FIELD_PASSWORD
                );

            $response = $this->sshExec($hostInfo, $command);
        } catch (HostException $e) {
            return $this->getIFTTT()->createResponse($e->getMessage(), 404);
        } catch (ErrorException $e) {
            return $this->getIFTTT()->createResponse($e->getMessage(), 422);
        }

        return $this->getIFTTT()->createResponse($response);
    }

    /**
     * SSH Exec command on given host
     *
     * @throws ErrorException
     */
    private function sshExec(ExtendedArray $hostInfo, string $command): string
    {
        $sshConnection = new SSH2($hostInfo->ipAddress, $hostInfo->port ?? 22);

        if (!$sshConnection->login($hostInfo->user, $hostInfo->password)) {
            throw new ErrorException(
                "Failed to login to host {$hostInfo->ipAddress}"
            );
        }

        return $sshConnection->exec($command);
    }
}
