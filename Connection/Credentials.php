<?php

declare(strict_types=1);

namespace MauticPlugin\LiveStormBundle\Connection;

use Mautic\IntegrationsBundle\Auth\Provider\AuthCredentialsInterface;

/**
 * Credentials class provides helper methods to fetch credentials.
 * For Livestorm API, we just need to pass the key for API calls.
 */
class Credentials implements AuthCredentialsInterface
{
    private $secret;

    /**
     * Credentials constructor.
     */
    public function __construct(string $secret)
    {
        $this->secret  = $secret;
    }

    public function getApiSecret(): ?string
    {
        return $this->secret;
    }
}
