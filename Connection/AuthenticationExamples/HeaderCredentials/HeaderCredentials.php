<?php

declare(strict_types=1);

namespace MauticPlugin\LiveStormBundle\Connection\AuthenticationExamples\HeaderCredentials;

use Mautic\IntegrationsBundle\Auth\Provider\ApiKey\Credentials\HeaderCredentialsInterface;

/**
 * Header Credentials example class.
 */
class HeaderCredentials implements HeaderCredentialsInterface
{
    private $secret;

    /**
     * Credentials constructor.
     */
    public function __construct(string $secret)
    {
        $this->secret  = $secret;
    }

    public function getKeyName(): string
    {
        return 'X-API-Key';
    }

    public function getApiKey(): ?string
    {
        return $this->secret;
    }
}
