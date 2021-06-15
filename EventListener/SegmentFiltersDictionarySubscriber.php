<?php

declare(strict_types=1);

namespace MauticPlugin\LiveStormBundle\EventListener;

use Mautic\LeadBundle\Event\SegmentDictionaryGenerationEvent;
use Mautic\LeadBundle\LeadEvents;
use MauticPlugin\LiveStormBundle\Integration\Config;
use MauticPlugin\LiveStormBundle\Segment\Query\Filter\EventAttendanceQueryBuilder;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * SegmentFiltersDictionarySubscriber class provides the mapping of filter to
 * query.
 * For ex: For choices provided by SegmentEventFiltersSubscriber class, we need
 * to map QueryBuilder.
 * Here, we are doing this query builder mapping process.
 *
 * "attended" segment filter will be mapped to the query builder service
 * provided by EventAttendanceQueryBuilder::getServiceId()
 */
class SegmentFiltersDictionarySubscriber implements EventSubscriberInterface
{
    /**
     * @var Config
     */
    private $config;

    /**
     * SegmentFiltersDictionarySubscriber constructor.
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * @return mixed[]
     */
    public static function getSubscribedEvents(): array
    {
        return [
            LeadEvents::SEGMENT_DICTIONARY_ON_GENERATE => 'onGenerateSegmentDictionary',
        ];
    }

    public function onGenerateSegmentDictionary(SegmentDictionaryGenerationEvent $event): void
    {
        $event->addTranslation(
            'attended',
            [
                'type'          => EventAttendanceQueryBuilder::getServiceId(),
                'field'         => 'lead',
                'table'         => 'sync_object_mapping',
            ]
        );
        $event->addTranslation(
            'did-not-attended',
            [
                'type'          => EventAttendanceQueryBuilder::getServiceId(),
                'field'         => 'lead',
                'table'         => 'sync_object_mapping',
            ]
        );
    }
}
