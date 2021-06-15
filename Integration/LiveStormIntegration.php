<?php

namespace MauticPlugin\LiveStormBundle\Integration;

use Mautic\IntegrationsBundle\Integration\BasicIntegration;
use Mautic\IntegrationsBundle\Integration\ConfigurationTrait;
use Mautic\IntegrationsBundle\Integration\Interfaces\BasicInterface;

/**
 * Class LiveStormIntegration.
 */
class LiveStormIntegration extends BasicIntegration implements BasicInterface
{
    use ConfigurationTrait;

    public const NAME                           = 'livestorm';
    public const DISPLAY_NAME                   = 'LiveStorm';
    public const EVENT_ATTENDANCE               = 'event_attendance';
    public const EVENT_DID_NOT_ATTENDED         = 'event_did_not_attended';
    public const EVENT_VIEWED_REPLY             = 'reply_view';
    public const SESSION_ATTENDANCE             = 'session_attendance';
    public const PARTICIPANT                    = 'participant';
    public const MESSAGES                       = 'messages';
    public const QUESTIONS                      = 'questions';
    public const VOTE                           = 'vote';
    public const UPVOTE                         = 'upvote';

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return self::NAME;
    }

    /**
     * Get the plugin display name.
     */
    public function getDisplayName(): string
    {
        return self::DISPLAY_NAME;
    }

    /**
     * Get the plugin icon path.
     */
    public function getIcon(): string
    {
        return 'plugins/LiveStormBundle/Assets/img/livestorm.png';
    }
}
