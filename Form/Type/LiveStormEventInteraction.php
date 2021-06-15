<?php

declare(strict_types=1);

namespace MauticPlugin\LiveStormBundle\Form\Type;

use MauticPlugin\LiveStormBundle\Integration\LiveStormIntegration;
use MauticPlugin\LiveStormBundle\Services\LiveStormApiHandler;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LiveStormEventInteraction extends AbstractType
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
            'plugin.livestorm.form.messages'   => LiveStormIntegration::MESSAGES,
            'plugin.livestorm.form.questions'  => LiveStormIntegration::QUESTIONS,
            'plugin.livestorm.form.vote'       => LiveStormIntegration::VOTE,
            'plugin.livestorm.form.upvote'     => LiveStormIntegration::UPVOTE,
        ];

        $builder->add(
            'event_interaction',
            ChoiceType::class,
            [
                'label'    => 'plugin.livestorm.form.event_interaction',
                'required' => true,
                'attr'     => [
                    'class' => 'form-control',
                ],
                'choices'  => $activities,
            ]
        );
        $formModifier = function (FormInterface $form, $data) use ($builder) {
            $form->add(
                $builder->create('event_interaction_count', NumberType::class, [
                    'label'      => 'plugin.livestorm.form.event_interaction.interaction_count',
                    'required'   => true,
                    'label_attr' => ['class' => 'control-label'],
                    'attr'       => [
                        'class'   => 'form-control',
                        'onBlur'  => 'Mautic.EnablesOption(this.id)',
                    ],
                    'auto_initialize' => false,
                ])
                    ->getForm()
            );
            $form->add(
                $builder->create('operator', ChoiceType::class, [
                    'label'         => 'plugin.livestorm.form.event_interaction.operator',
                    'required'      => true,
                    'label_attr'    => ['class' => 'control-label'],
                    'choices'       => [
                        'Less than'           => 'lt',
                        'Less than equals'    => 'lte',
                        'Greater than'        => 'gt',
                        'Greater than equals' => 'gte',
                        'Equals'              => 'eq',
                    ],
                    'auto_initialize' => false,
                ])
                    ->getForm()
            );
        };

        $builder->addEventListener(FormEvents::PRE_SUBMIT,
            function (FormEvent $event) use ($formModifier) {
                $data = $event->getData();
                $formModifier($event->getForm(), $data);
            }
        );
        $builder->addEventListener(FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($formModifier) {
                $data = $event->getData();
                $formModifier($event->getForm(), $data);
            }
        );
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $optionsResolver)
    {
        $optionsResolver->setDefaults(
            [
                'event'                   => null,
                'event_interaction'       => null,
                'event_interaction_count' => null,
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
