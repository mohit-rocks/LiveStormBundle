<?php

namespace MauticPlugin\LiveStormBundle\Connection;

use MauticPlugin\LiveStormBundle\Integration\Config;
use MauticPlugin\LiveStormBundle\Integration\LiveStormIntegration;
use Monolog\Logger;

class ApiConsumer
{
    /**
     * @var \MauticPlugin\LiveStormBundle\Connection\Client
     */
    private $client;

    public function __construct(
        Logger $logger,
        Client $client,
        Config $config
    ) {
        $this->logger           = $logger;
        $this->client           = $client;
        $this->config           = $config;
    }

    /**
     * Fetch all the events.
     */
    public function fetchEvents()
    {
        return $this->client->get('/events');
    }

    /**
     * Get event information for the event.
     *
     * @param $eventId
     *
     * @return array
     */
    public function fetchEventInformation($eventId = null)
    {
        $event = $this->client->get('/events/'.$eventId);

        return $event;
    }

    /**
     * Get the list of all the participants for the event.
     *
     * @param string $eventId
     *                        Event Id
     *
     * @return array
     *               List of all the participants
     */
    public function fetchEventParticipants(string $eventId)
    {
        return $this->client->get('/events/'.$eventId.'/people');
    }

    /**
     * Get the list of all the sessions in an event.
     *
     * @param string $eventId
     *                        Event Id
     *
     * @return array
     *               List of all the session for given event
     */
    public function fetchEventSessions(string $eventId)
    {
        return $this->client->get('/events/'.$eventId.'/sessions');
    }

    /**
     * Get the list of all the participants for the session.
     *
     * Session API endpoints contains more information about the participants
     * like whether they attended or not, messages, questions, votes etc.
     *
     * @param string $sessionId
     *                          Event Id
     *
     * @return array
     *               List of all the participants
     */
    public function fetchSessionParticipants(string $sessionId)
    {
        return $this->client->get('/sessions/'.$sessionId.'/people');
    }
}
