<?php

declare(strict_types=1);

namespace MauticPlugin\LiveStormBundle\Segment\Query\Filter;

use Doctrine\DBAL\ParameterType;
use Mautic\LeadBundle\Segment\ContactSegmentFilter;
use Mautic\LeadBundle\Segment\Query\Filter\BaseFilterQueryBuilder;
use Mautic\LeadBundle\Segment\Query\QueryBuilder;
use MauticPlugin\LiveStormBundle\Integration\LiveStormIntegration;

class EventAttendanceQueryBuilder extends BaseFilterQueryBuilder
{
    public static function getServiceId(): string
    {
        return 'livestorm.integration.query.builder.event.attendance';
    }

    public function applyQuery(QueryBuilder $queryBuilder, ContactSegmentFilter $filter): QueryBuilder
    {
        $filterOperator = $filter->getOperator();
        $filterValue    = $filter->getParameterValue();
        // Giving static query alias, as we want to retrieve parameter value in
        // other filter.
        $queryAlias     = 'livestorm_event_attendance';

        $activity = $filter->contactSegmentFilterCrate->getField();
        switch ($activity) {
            case 'attended':
                $integrationReference = $filterValue.':true';
                break;

            case 'did-not-attended':
                $integrationReference = $filterValue.':false';
                break;

            default:
                $integrationReference = null;
        }

        $filterQueryBuilder = $queryBuilder->createQueryBuilder();
        $queryBuilder->setParameter(
            $queryAlias.'_integration_value_reference',
            $integrationReference,
            (is_array($filter->getParameterValue()) ? \Doctrine\DBAL\Connection::PARAM_STR_ARRAY : ParameterType::STRING)
        );

        $filterQueryBuilder
            ->select('id')
            ->from(MAUTIC_TABLE_PREFIX.'sync_object_mapping', $queryAlias)
            ->where(
                $filterQueryBuilder->expr()->eq(
                    $queryAlias.'.integration',
                    $filterQueryBuilder->expr()->literal(LiveStormIntegration::NAME)
                )
            )
            ->andWhere(
                $filterQueryBuilder->expr()->eq(
                    $queryAlias.'.integration_object_name',
                    $filterQueryBuilder->expr()->literal(LiveStormIntegration::EVENT_ATTENDANCE)
                )
            )
            ->andWhere(
                $filterQueryBuilder->expr()->eq(
                    $queryAlias.'.integration_reference_id',
                    ':'.$queryAlias.'_integration_value_reference',
                )
            );
        $filterQueryBuilder
            ->andWhere(
                $filterQueryBuilder->expr()->eq($queryAlias.'.is_deleted', $filterQueryBuilder->expr()->literal(0))
            )
            ->andWhere(
                $queryBuilder->expr()->eq('l.id', 'internal_object_id')
            );

        switch ($filterOperator) {
            case 'neq':
                $queryBuilder->addLogic($queryBuilder->expr()->notExists($filterQueryBuilder->getSQL()), $filter->getGlue());
                $filterQueryBuilder->orWhere($queryBuilder->expr()->isNull($queryAlias.'.integration_object_name'));

                break;
            default:
                $queryBuilder->addLogic($queryBuilder->expr()->exists($filterQueryBuilder->getSQL()), $filter->getGlue());
        }

        return $queryBuilder;
    }
}
