<?php

declare(strict_types=1);

namespace MauticPlugin\LiveStormBundle\Connection\AuthenticationExamples\BasicAuthCredentials;

use Mautic\IntegrationsBundle\Auth\Provider\BasicAuth\CredentialsInterface;

/**
 * Basic Auth Credentials example class.
 */
class BasicAuthCredentials implements CredentialsInterface
{
    private $userName;

    private $password;

    /**
     * Credentials constructor.
     */
    public function __construct(string $userName, string $password)
    {
        $this->userName = $userName;
        $this->password = $password;
    }

    public function getUsername(): ?string
    {
        return $this->userName;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }
}
