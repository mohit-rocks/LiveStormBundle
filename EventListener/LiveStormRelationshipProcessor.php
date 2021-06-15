<?php

namespace MauticPlugin\LiveStormBundle\EventListener;

use Mautic\IntegrationsBundle\Event\CompletedSyncIterationEvent;
use Mautic\IntegrationsBundle\IntegrationEvents;
use MauticPlugin\LiveStormBundle\Integration\LiveStormIntegration;
use MauticPlugin\LiveStormBundle\Services\LiveStormApiHandler;
use MauticPlugin\LiveStormBundle\Services\SyncObjectProcessor;
use MauticPlugin\LiveStormBundle\Sync\Mapping\Manual\MappingManualFactory;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class LiveStormRelationshipProcessor implements EventSubscriberInterface
{
    /**
     * @var LiveStormApiHandler
     */
    private $liveStormApiHandler;

    /**
     * @var SyncObjectProcessor
     */
    private $syncObjectProcessor;

    public function __construct(LiveStormApiHandler $liveStormApiHandler, SyncObjectProcessor $syncObjectProcessor)
    {
        $this->liveStormApiHandler     = $liveStormApiHandler;
        $this->syncObjectProcessor     = $syncObjectProcessor;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            IntegrationEvents::INTEGRATION_BATCH_SYNC_COMPLETED_INTEGRATION_TO_MAUTIC => 'onSyncComplete',
        ];
    }

    /**
     * Handle additional relationships like data and object mapping for
     * "sync_object_mapping" table.
     */
    public function onSyncComplete(CompletedSyncIterationEvent $event)
    {
        if (LiveStormIntegration::NAME == $event->getIntegration()) {
            $orderResult    = $event->getOrderResults();
            $createdObjects = $orderResult->getObjectMappings(MappingManualFactory::LIVESTORM_OBJECT);

            if (!empty($createdObjects)) {
                // Fetch a list of objects objects from the integration's API.
                $participants = $this->fetchAllParticipantsData();

                foreach ($createdObjects as $object) {
                    $integrationObjectId       = $object->getIntegrationObjectId();
                    $mauticObjectId            = $object->getInternalObjectId();
                    $this->syncObjectProcessor->syncRelations($mauticObjectId, $integrationObjectId, $participants);
                }
            }
        }
    }

    /**
     * Fetch all the participants data from all the events.
     * Primarily we will be fetching from cache only.
     *
     * @return array
     */
    public function fetchAllParticipantsData()
    {
        $events       = $this->liveStormApiHandler->getAllEvents();
        $participants = [];
        foreach ($events as $event) {
            $participantsData = $this->liveStormApiHandler->getAllParticipants($event['id']);
            $participants     = array_merge($participants, $participantsData);
        }

        return $participants;
    }
}
