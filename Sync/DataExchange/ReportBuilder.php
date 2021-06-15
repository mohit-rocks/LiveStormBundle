<?php

declare(strict_types=1);

namespace MauticPlugin\LiveStormBundle\Sync\DataExchange;

use Mautic\IntegrationsBundle\Sync\DAO\Sync\InputOptionsDAO;
use Mautic\IntegrationsBundle\Sync\DAO\Sync\Report\FieldDAO;
use Mautic\IntegrationsBundle\Sync\DAO\Sync\Report\ObjectDAO as ReportObjectDAO;
use Mautic\IntegrationsBundle\Sync\DAO\Sync\Report\ReportDAO;
use Mautic\IntegrationsBundle\Sync\DAO\Sync\Request\ObjectDAO as RequestObjectDAO;
use MauticPlugin\LiveStormBundle\Integration\Config;
use MauticPlugin\LiveStormBundle\Integration\LiveStormIntegration;
use MauticPlugin\LiveStormBundle\Services\LiveStormApiHandler;
use MauticPlugin\LiveStormBundle\Sync\DataExchange\ValueNormalizer;
use MauticPlugin\LiveStormBundle\Sync\Mapping\Field\Field;
use MauticPlugin\LiveStormBundle\Sync\Mapping\Field\FieldRepository;

class ReportBuilder
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var FieldRepository
     */
    private $fieldRepository;

    /**
     * @var LiveStormApiHandler
     */
    private $liveStormApiHandler;

    /**
     * @var ReportDAO
     */
    private $report;

    /**
     * @var ValueNormalizer
     */
    private $valueNormalizer;

    public function __construct(Config $config, FieldRepository $fieldRepository, LiveStormApiHandler $liveStormApiHandler)
    {
        $this->config                  = $config;
        $this->fieldRepository         = $fieldRepository;
        $this->liveStormApiHandler     = $liveStormApiHandler;
        $this->valueNormalizer         = new ValueNormalizer();
    }

    /**
     * @param RequestObjectDAO[] $requestedObjects
     */
    public function build(int $page, array $requestedObjects, InputOptionsDAO $options): ReportDAO
    {
        $this->report = new ReportDAO(LiveStormIntegration::NAME);

        // @todo: Introduce pager support.
        $events       = $this->liveStormApiHandler->getAllEvents();
        $participants = [];
        foreach ($events as $event) {
            $participantsData = $this->liveStormApiHandler->getAllParticipants($event['id']);
            $participants     = array_merge($participants, $participantsData);
        }

        foreach ($requestedObjects as $requestedObject) {
            $objectName = $requestedObject->getObject();

            // Add the modified items to the report
            $this->addModifiedItems($objectName, $participants);
        }

        return $this->report;
    }

    private function addModifiedItems(string $objectName, array $changeList): void
    {
        // Get the the field list to know what the field types are.
        $fields = $this->fieldRepository->getFields($objectName);

        $mappedFields = $this->config->getMappedFields($objectName);

        foreach ($changeList as $item) {
            $objectDAO = new ReportObjectDAO(
                $objectName,
                // Set the ID from the integration.
                $item['id']
            );

            foreach ($item['attributes']['registrant_detail']['fields'] as $userField) {
                $fieldAlias = $userField['id'];
                $fieldValue = $userField['value'];
                if (!isset($fields[$fieldAlias]) || !isset($mappedFields[$fieldAlias])) {
                    // Field is not recognized or it's not mapped so ignore.
                    continue;
                }

                $field = $fields[$fieldAlias];

                // The sync is currently from LiveStorm to Mautic so normalize
                // the values for storage in Mautic.
                $normalizedValue = $this->valueNormalizer->normalizeForMautic(
                    $fieldValue,
                    $field->getDataType()
                );

                $objectDAO->addField(new FieldDAO($fieldAlias, $normalizedValue));
            }

            // Add the modified/new lead to the report.
            $this->report->addObject($objectDAO);
        }
    }
}
