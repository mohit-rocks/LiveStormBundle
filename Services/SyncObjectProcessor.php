<?php

namespace MauticPlugin\LiveStormBundle\Services;

use Mautic\IntegrationsBundle\Entity\ObjectMapping;
use Mautic\IntegrationsBundle\Entity\ObjectMappingRepository;
use Mautic\IntegrationsBundle\Sync\SyncDataExchange\Internal\Object\Contact;
use Mautic\LeadBundle\Model\LeadModel;
use Mautic\PointBundle\Model\PointModel;
use MauticPlugin\LiveStormBundle\EventListener\WebinarAttendancePointsSubscriber;
use MauticPlugin\LiveStormBundle\EventListener\WebinarInteractionPointsSubscriber;
use MauticPlugin\LiveStormBundle\Integration\LiveStormIntegration;

class SyncObjectProcessor
{
    /**
     * @var SyncObjectMapping
     */
    private $syncObjectMapping;

    /**
     * @var LeadModel
     */
    private $leadModel;

    /**
     * @var PointModel
     */
    private $pointModel;

    /**
     * @var ObjectMappingRepository
     */
    private $objectMappingRepository;

    public function __construct(SyncObjectMapping $syncObjectMapping, LeadModel $lead_model, PointModel $pointModel, ObjectMappingRepository $objectMappingRepository)
    {
        $this->syncObjectMapping       = $syncObjectMapping;
        $this->leadModel               = $lead_model;
        $this->pointModel              = $pointModel;
        $this->objectMappingRepository = $objectMappingRepository;
    }

    /**
     * Store relation of lead and sync data in the integrations table.
     *
     * @param string $mauticObjectId
     *                                    Mautic lead id
     * @param string $integrationObjectId
     *                                    Integration object id
     * @param array  $participants
     *                                    Data array from API
     *
     * @throws \Exception
     */
    public function syncRelations(string $mauticObjectId, string $integrationObjectId, array $participants)
    {
        $userData = array_filter($participants, function ($value) use ($integrationObjectId) {
            return $value['id'] == $integrationObjectId;
        });
        foreach ($userData as $user) {
            // Store the mapping of participant's attendance as a relationship.
            // In Livestorm, one event can have multiple sessions.
            // So we are marking event as attended even if user attends one session.

            // Storing attendance status as text so we can use in filters etc.
            $attendance_status = true === $user['attributes']['registrant_detail']['attended'] ? 'true' : 'false';
            $this->mapContactObject(
                $integrationObjectId,
                $mauticObjectId,
                LiveStormIntegration::EVENT_ATTENDANCE,
                $user['attributes']['registrant_detail']['event_id'].':'.$attendance_status,
                $user['attributes']['registrant_detail']['event_id'],
            );
            // Store additional attributes in integration.
            $this->mapContactObject(
                $integrationObjectId,
                $mauticObjectId,
                LiveStormIntegration::MESSAGES,
                $user['attributes']['registrant_detail']['event_id'].':'.$user['attributes']['messages_count'],
                $user['attributes']['registrant_detail']['event_id'],
            );
            $this->mapContactObject(
                $integrationObjectId,
                $mauticObjectId,
                LiveStormIntegration::QUESTIONS,
                $user['attributes']['registrant_detail']['event_id'].':'.$user['attributes']['questions_count'],
                $user['attributes']['registrant_detail']['event_id'],
            );
            $this->mapContactObject(
                $integrationObjectId,
                $mauticObjectId,
                LiveStormIntegration::VOTE,
                $user['attributes']['registrant_detail']['event_id'].':'.$user['attributes']['votes_count'],
                $user['attributes']['registrant_detail']['event_id'],
            );
            $this->mapContactObject(
                $integrationObjectId,
                $mauticObjectId,
                LiveStormIntegration::UPVOTE,
                $user['attributes']['registrant_detail']['event_id'].':'.$user['attributes']['up_votes_count'],
                $user['attributes']['registrant_detail']['event_id'],
            );

//          $this->mapContactObject(
//              $integrationObjectId,
//              $mauticObjectId,
//              LiveStormIntegration::SESSION_ATTENDANCE,
//              $userData['attributes']['registrant_detail']['session_id'] . ':' . $attendance_status
//          );

            // Assign points to contacts based on event attendance.
            $this->addPointsOnEventAttendance($mauticObjectId, $user['attributes']['registrant_detail']['event_id'], $attendance_status);

            // Prepare all the interactions array and pass to seperate method to allocate points based on that.
            $interactions = [
                LiveStormIntegration::MESSAGES  => $user['attributes']['messages_count'],
                LiveStormIntegration::QUESTIONS => $user['attributes']['questions_count'],
                LiveStormIntegration::VOTE      => $user['attributes']['votes_count'],
                LiveStormIntegration::UPVOTE    => $user['attributes']['up_votes_count'],
            ];
            $this->addPointsOnEventInteraction($mauticObjectId, $user['attributes']['registrant_detail']['event_id'], $interactions);
        }
    }

    /**
     * Map and store the integration in the table.
     *
     * @param $integrationId
     * @param $mauticId
     * @param $integrationObjectName
     * @param null $integrationReferenceId
     *
     * @throws \Exception
     */
    private function mapContactObject($integrationId, $mauticId, $integrationObjectName, $integrationReferenceId = null, $eventId)
    {
        $integrationObject = $this->syncObjectMapping->getMappingExistence(
            $mauticId,
            $integrationObjectName,
            $integrationId,
            $eventId
        );

        if (is_null($integrationObject)) {
            $objectMapping = new ObjectMapping();
            $objectMapping->setIntegration(LiveStormIntegration::NAME)
                ->setIntegrationObjectName($integrationObjectName)
                ->setInternalObjectName(Contact::NAME)
                ->setIntegrationObjectId($integrationId)
                ->setInternalObjectId($mauticId)
                ->setLastSyncDate(new \DateTime());

            if ($integrationReferenceId) {
                $objectMapping->setIntegrationReferenceId($integrationReferenceId);
            }
            $this->saveObjectMapping($objectMapping);
        } else {
            $mappingEntityId = $integrationObject['id'];
            /** @var ObjectMapping $updateObjectMapping */
            $updateObjectMapping  = $this->objectMappingRepository->getEntity($mappingEntityId);

            if (!empty($integrationReferenceId) &&
                $integrationReferenceId !== $updateObjectMapping->getIntegrationReferenceId()
            ) {
                $updateObjectMapping->setIntegrationReferenceId($integrationReferenceId);
                $updateObjectMapping->setLastSyncDate(new \DateTime());
                $this->saveObjectMapping($updateObjectMapping);
            }
        }
    }

    /**
     * Save the object mapping.
     */
    private function saveObjectMapping(ObjectMapping $objectMapping): void
    {
        $this->objectMappingRepository->saveEntity($objectMapping);
        $this->objectMappingRepository->clear();
    }

    /**
     * Assign points to user based on defined activity.
     *
     * @param int    $leadId
     *                         Mautic contact id
     * @param string $eventId
     *                         UUID of the event
     * @param string $activity
     *                         Event activity and values for which we want to assign points
     */
    private function addPointsOnEventAttendance($leadId, $eventId, $activity)
    {
        $activity = 'true' === $activity ? LiveStormIntegration::EVENT_ATTENDANCE : LiveStormIntegration::EVENT_DID_NOT_ATTENDED;
        $data     = ['event_id' => $eventId, 'activity' => $activity];
        $lead     = $this->leadModel->getEntity($leadId);
        $this->pointModel->triggerAction(WebinarAttendancePointsSubscriber::LIVESTORM_EVENT_ACTIVITIES_POINTS, $data, null, $lead);
    }

    /**
     * Assign points to users based on interactions during the webinar.
     *
     * @param int    $leadId
     *                             Mautic contact id
     * @param string $eventId
     *                             UUID of the event
     * @param array  $interactions
     *                             All the interaction of the user for the event
     */
    private function addPointsOnEventInteraction($leadId, $eventId, $interactions)
    {
        $lead     = $this->leadModel->getEntity($leadId);
        $data     = ['event_id' => $eventId, 'interactions' => $interactions];
        $this->pointModel->triggerAction(WebinarInteractionPointsSubscriber::LIVESTORM_EVENT_INTERACTION_POINTS, $data, null, $lead);
    }
}
