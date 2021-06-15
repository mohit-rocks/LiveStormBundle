<?php

declare(strict_types=1);

namespace MauticPlugin\LiveStormBundle\EventListener;

use Mautic\PointBundle\Event\PointBuilderEvent;
use Mautic\PointBundle\Model\PointModel;
use Mautic\PointBundle\PointEvents;
use MauticPlugin\LiveStormBundle\Form\Type\LiveStormEventAttendance;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class WebinarAttendancePointsSubscriber implements EventSubscriberInterface
{
    public const LIVESTORM_EVENT_ACTIVITIES_POINTS = 'livestorm.event.activities.points';

    public function __construct(PointModel $pointModel)
    {
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            PointEvents::POINT_ON_BUILD => ['onPointActionFormBuild', 0],
        ];
    }

    public function onPointActionFormBuild(PointBuilderEvent $event): void
    {
        $action = [
            'group'       => 'plugin.livestorm.points.group',
            'label'       => 'plugin.livestorm.points.group.activities',
            'callback'    => [self::class, 'addPointTriggerCallback'],
            'formType'    => LiveStormEventAttendance::class,
        ];

        $event->addAction(self::LIVESTORM_EVENT_ACTIVITIES_POINTS, $action);
    }

    public static function addPointTriggerCallback(array $action, array $eventDetails): bool
    {
        return $action['properties']['event'] === $eventDetails['event_id'] &&
            $action['properties']['event_activity'] === $eventDetails['activity'];
    }
}
