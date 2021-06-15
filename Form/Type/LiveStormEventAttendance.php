<?php

declare(strict_types=1);

namespace MauticPlugin\LiveStormBundle\Form\Type;

use MauticPlugin\LiveStormBundle\Integration\LiveStormIntegration;
use MauticPlugin\LiveStormBundle\Services\LiveStormApiHandler;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LiveStormEventAttendance extends AbstractType
{
    /**
     * @var LiveStormApiHandler
     */
    private $liveStormApiHandler;

    /**
     * {@inheritdoc}
     */
    public function __construct(LiveStormApiHandler $liveStormApiHandler)
    {
        $this->liveStormApiHandler = $liveStormApiHandler;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'event',
            ChoiceType::class,
            [
                'label'    => 'plugin.livestorm.form.event_list',
                'required' => true,
                'attr'     => [
                    'class' => 'form-control',
                ],
                'choices'  => $this->getEventsList(),
            ]
        );
        $activities = [
            'plugin.livestorm.form.attended'          => LiveStormIntegration::EVENT_ATTENDANCE,
            'plugin.livestorm.form.did-not-attended'  => LiveStormIntegration::EVENT_DID_NOT_ATTENDED,
            'plugin.livestorm.form.viewed_reply'      => LiveStormIntegration::EVENT_VIEWED_REPLY,
        ];

        $builder->add(
            'event_activity',
            ChoiceType::class,
            [
                'label'    => 'plugin.livestorm.form.event_activities',
                'required' => true,
                'attr'     => [
                    'class' => 'form-control',
                ],
                'choices'  => $activities,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $optionsResolver)
    {
        $optionsResolver->setDefaults(
            [
                'event_activity' => null,
                'event'          => null,
            ]
        );
    }

    /**
     * Get the list of all the events as an options array.
     */
    private function getEventsList(): array
    {
        $events   = $this->liveStormApiHandler->getAllEvents();
        $options  = [];

        foreach ($events as $event) {
            $options[$event['attributes']['title']] = $event['id'];
        }

        return $options;
    }
}
