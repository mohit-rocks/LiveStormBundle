<?php

declare(strict_types=1);

namespace MauticPlugin\LiveStormBundle\EventListener;

use Mautic\PointBundle\Event\PointBuilderEvent;
use Mautic\PointBundle\Model\PointModel;
use Mautic\PointBundle\PointEvents;
use MauticPlugin\LiveStormBundle\Form\Type\LiveStormEventInteraction;
use MauticPlugin\LiveStormBundle\Helper\LiveStormPointActionHelper;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class WebinarInteractionPointsSubscriber implements EventSubscriberInterface
{
    public const LIVESTORM_EVENT_INTERACTION_POINTS = 'livestorm.event.interaction.points';

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
            'label'       => 'plugin.livestorm.points.group.interactions',
            'callback'    => [LiveStormPointActionHelper::class, 'validateUserInteraction'],
            'formType'    => LiveStormEventInteraction::class,
        ];

        $event->addAction(self::LIVESTORM_EVENT_INTERACTION_POINTS, $action);
    }
}
