<?php

namespace MauticPlugin\LiveStormBundle\Services;

use MauticPlugin\LiveStormBundle\Connection\ApiConsumer;
use Symfony\Component\Cache\Simple\FilesystemCache;

class LiveStormApiHandler
{
    /**
     * @var ApiConsumer
     */
    private $apiConsumer;

    /**
     * @var \Symfony\Component\Cache\Simple\FilesystemCache
     */
    private $cache;

    /**
     * LiveStormApiHandler constructor.
     */
    public function __construct(ApiConsumer $apiConsumer)
    {
        $this->apiConsumer   = $apiConsumer;
        $this->cache         = new FilesystemCache();
    }

    /**
     * Fetch the list of all the events for the account.
     *
     * @return array
     *               List of all the events
     */
    public function getAllEvents(): array
    {
        $events = $this->cache->get('livestorm.api.events');
        if (null !== $events) {
            return $events;
        }
        $events = $this->apiConsumer->fetchEvents();
        $this->cache->set('livestorm.api.events', $events, 60);

        return $events;
    }

    /**
     * Get all participants for given webinar event.
     *
     * @return array
     *               List of all the participants
     */
    public function getAllParticipants(string $eventId): array
    {
        $sessions = $this->getAllSessions($eventId);

        // Create unique cache key per event and store results accordingly.
        $cacheKey = 'livestorm.api.event.sessions.participants.'.$eventId;

        // Check the participants in cache key and return from there if exists.
        $participants = $this->cache->get($cacheKey);
        if (null !== $participants) {
            return $participants;
        }

        // Fetch the participants/registrations for each sessions.
        $participants = [];
        foreach ($sessions as $session) {
            $peoples = $this->apiConsumer->fetchSessionParticipants($session['id']);
            foreach ($peoples as $participant) {
                $participants[] = $participant;
            }
        }
        // Store the value in cache.
        $this->cache->set($cacheKey, $participants, 60);

        return $participants;
    }

    /**
     * Fetch the list of all the sessions for given event.
     *
     * @param string $eventId
     *                        Event id
     *
     * @return array
     *               List of all the sessions for given event
     */
    public function getAllSessions(string $eventId): array
    {
        // Create unique cache key per event and store results accordingly.
        $cacheKey = 'livestorm.api.events.sessions.'.$eventId;
        $sessions = $this->cache->get($cacheKey);
        if (null !== $sessions) {
            return $sessions;
        }
        $sessions = $this->apiConsumer->fetchEventSessions($eventId);
        $this->cache->set($cacheKey, $sessions, 60);

        return $sessions;
    }
}
