<?php

declare(strict_types=1);

namespace MauticPlugin\LiveStormBundle\EventListener;

use Mautic\LeadBundle\Entity\OperatorListTrait;
use Mautic\LeadBundle\Event\LeadListFiltersChoicesEvent;
use Mautic\LeadBundle\LeadEvents;
use MauticPlugin\LiveStormBundle\Integration\Config;
use MauticPlugin\LiveStormBundle\Services\LiveStormApiHandler;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Translation\TranslatorInterface;

class SegmentEventFiltersSubscriber implements EventSubscriberInterface
{
    use OperatorListTrait;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var \MauticPlugin\LiveStormBundle\Services\LiveStormApiHandler
     */
    private $liveStormApiHandler;

    public function __construct(
        Config $config,
        TranslatorInterface $translator,
        LiveStormApiHandler $liveStormApiHandler
    ) {
        $this->config              = $config;
        $this->translator          = $translator;
        $this->liveStormApiHandler = $liveStormApiHandler;
    }

    /**
     * @return mixed[]
     */
    public static function getSubscribedEvents(): array
    {
        return [LeadEvents::LIST_FILTERS_CHOICES_ON_GENERATE => 'onGenerateSegmentFilters'];
    }

    /**
     * Add Contact filters.
     */
    public function onGenerateSegmentFilters(LeadListFiltersChoicesEvent $event): void
    {
        $this->addEventAttendanceFilter($event);
    }

    /**
     * Add new filters for event's attendance status.
     */
    private function addEventAttendanceFilter(LeadListFiltersChoicesEvent $event): void
    {
        $eventNames = $this->getEventNames();

        $event->addChoice(
            'lead',
            'attended',
            [
                'label'      => $this->translator->trans('plugin.livestrom.form.attended'),
                'properties' => [
                    'type' => 'select',
                    'list' => $eventNames,
                ],
                'operators' => [
                    'eq'  => $this->translator->trans('mautic.core.operator.equals'),
                    '!eq' => $this->translator->trans('mautic.core.operator.notequals'),
                ],
            ]
        );
        $event->addChoice(
            'lead',
            'did-not-attended',
            [
                'label'      => $this->translator->trans('plugin.livestrom.form.did-not-attended'),
                'properties' => [
                    'type' => 'select',
                    'list' => $eventNames,
                ],
                'operators' => [
                    'eq'  => $this->translator->trans('mautic.core.operator.equals'),
                    '!eq' => $this->translator->trans('mautic.core.operator.notequals'),
                ],
            ]
        );
    }

    /**
     * Get the list of all the events.
     */
    public function getEventNames(): array
    {
        $events   = $this->liveStormApiHandler->getAllEvents();
        $options  = [];

        foreach ($events as $event) {
            $options[$event['id']] = $event['attributes']['title'];
        }

        return $options;
    }
}
