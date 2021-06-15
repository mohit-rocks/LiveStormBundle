<?php

declare(strict_types=1);

namespace MauticPlugin\LiveStormBundle\Connection\AuthenticationExamples\ParameterCredentials;

use Mautic\IntegrationsBundle\Auth\Provider\ApiKey\Credentials\ParameterCredentialsInterface;

/**
 * Parameter Credentials example class.
 */
class ParameterCredentials implements ParameterCredentialsInterface
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
        return 'apiKey';
    }

    public function getApiKey(): ?string
    {
        return $this->secret;
    }
}
