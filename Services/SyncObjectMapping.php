<?php

namespace MauticPlugin\LiveStormBundle\Services;

use Doctrine\DBAL\Connection;
use Mautic\IntegrationsBundle\Sync\SyncDataExchange\Internal\Object\Contact;
use MauticPlugin\LiveStormBundle\Integration\LiveStormIntegration;

class SyncObjectMapping
{
    /**
     * @var Connection
     */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Check to ensure that there is an existing mapping stored for a contact.
     *
     * @param $internalObjectId
     * @param $integrationObjectName
     * @param $integrationObjectId
     * @param $integrationReferenceId
     *
     * @return mixed|null
     */
    public function getMappingExistence($internalObjectId, $integrationObjectName, $integrationObjectId, $integrationReferenceId)
    {
        $qb = $this->connection->createQueryBuilder();
        $qb->select('*');
        $qb->from(MAUTIC_TABLE_PREFIX.'sync_object_mapping', 'livestorm');
        $qb->where('livestorm.integration = :integration');
        $qb->andWhere('livestorm.internal_object_name = :internalObjectName');
        $qb->andWhere('livestorm.internal_object_id = :internalObjectId');
        $qb->andWhere('livestorm.integration_object_name = :integrationObjectName');
        $qb->andWhere('livestorm.integration_object_id = :integrationObjectId');
        $qb->andWhere('livestorm.integration_reference_id LIKE :integrationReferenceId');

        $qb->setParameters([
            'integration'           => LiveStormIntegration::NAME,
            'internalObjectName'    => Contact::NAME,
            'internalObjectId'      => $internalObjectId,
            'integrationObjectName' => $integrationObjectName,
            'integrationObjectId'   => $integrationObjectId,
            'integrationReferenceId'=> $integrationReferenceId.'%',
        ]);

        $result = $qb->execute()->fetch();

        return $result ?: null;
    }
}
