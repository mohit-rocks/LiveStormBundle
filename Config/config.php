<?php

declare(strict_types=1);

return [
    'name'        => 'LiveStorm',
    'description' => 'Enables integration with LiveStorm API.',
    'version'     => '1.0.0',
    'author'      => 'Mohit Aghera.',
    'routes'      => [
        'main'   => [],
        'public' => [],
        'api'    => [],
    ],
    'services' => [
        'integrations' => [
            // Basic definitions with name, display name and icon
            'mautic.integration.livestorm' => [
                'class' => \MauticPlugin\LiveStormBundle\Integration\LiveStormIntegration::class,
                'tags'  => [
                    'mautic.integration',
                    'mautic.basic_integration',
                ],
            ],
            // Provides the form types to use for the configuration UI
            'livestorm.integration.configuration' => [
                'class'     => \MauticPlugin\LiveStormBundle\Integration\Support\ConfigSupport::class,
                'arguments' => [
                    'livestorm.sync.repository.fields',
                ],
                'tags'      => [
                    'mautic.config_integration',
                ],
            ],
            // Defines the mapping manual and sync data exchange service for the sync engine
            'livestorm.integration.sync' => [
                'class'     => \MauticPlugin\LiveStormBundle\Integration\Support\SyncSupport::class,
                'arguments' => [
                    'livestorm.sync.mapping_manual.factory',
                    'livestorm.sync.data_exchange',
                ],
                'tags'      => [
                    'mautic.sync_integration',
                ],
            ],
        ],
        'sync' => [
            'livestorm.sync.repository.fields' => [
                'class'     => \MauticPlugin\LiveStormBundle\Sync\Mapping\Field\FieldRepository::class,
            ],
            'livestorm.sync.mapping_manual.factory' => [
                'class'     => \MauticPlugin\LiveStormBundle\Sync\Mapping\Manual\MappingManualFactory::class,
                'arguments' => [
                    'livestorm.sync.repository.fields',
                    'livestorm.integration.config',
                ],
            ],
            'livestorm.sync.data_exchange' => [
                'class'     => \MauticPlugin\LiveStormBundle\Sync\DataExchange\SyncDataExchange::class,
                'arguments' => [
                    'livestorm.sync.data_exchange.report_builder',
                ],
            ],
            // Builds a report of updated and new objects from the integration to sync with Mautic
            'livestorm.sync.data_exchange.report_builder' => [
                'class'     => \MauticPlugin\LiveStormBundle\Sync\DataExchange\ReportBuilder::class,
                'arguments' => [
                    'livestorm.integration.config',
                    'livestorm.sync.repository.fields',
                    'livestorm.service.sync.contact_api_handler',
                ],
            ],
        ],
        'other' => [
            // Provides access to configured API keys, settings, field mapping, etc
            'livestorm.integration.config' => [
                'class'     => \MauticPlugin\LiveStormBundle\Integration\Config::class,
                'arguments' => [
                    'mautic.integrations.helper',
                ],
            ],
            'livestorm.connection.client' => [
                'class'     => \MauticPlugin\LiveStormBundle\Connection\Client::class,
                'arguments' => [
                    'monolog.logger.mautic',
                    'livestorm.integration.config',
                ],
            ],
            'livestorm.connection.client.api_consumer' => [
                'class'     => \MauticPlugin\LiveStormBundle\Connection\ApiConsumer::class,
                'arguments' => [
                    'monolog.logger.mautic',
                    'livestorm.connection.client',
                    'livestorm.integration.config',
                ],
            ],
            'livestorm.integration.query.builder.event.attendance' => [
                'class'     => \MauticPlugin\LiveStormBundle\Segment\Query\Filter\EventAttendanceQueryBuilder::class,
                'arguments' => [
                    'mautic.lead.model.random_parameter_name',
                    'event_dispatcher',
                ],
            ],
            'livestorm.integration.query.builder.event.interaction' => [
                'class'     => \MauticPlugin\LiveStormBundle\Segment\Query\Filter\EventInteractionQueryBuilder::class,
                'arguments' => [
                    'mautic.lead.model.random_parameter_name',
                    'event_dispatcher',
                ],
            ],
        ],
        'forms' => [
            'livestorm.integration.form.config_auth' => [
                'class'     => \MauticPlugin\LiveStormBundle\Form\Type\ConfigAuthType::class,
                'arguments' => [
                    'livestorm.connection.client',
                ],
            ],
            'livestorm.integration.form.webcast_attendance' => [
                'class'     => \MauticPlugin\LiveStormBundle\Form\Type\LiveStormEventAttendance::class,
                'arguments' => [
                    'livestorm.service.sync.contact_api_handler',
                ],
            ],
            'livestorm.integration.form.webcast_interaction' => [
                'class'     => \MauticPlugin\LiveStormBundle\Form\Type\LiveStormEventInteraction::class,
                'arguments' => [
                    'livestorm.service.sync.contact_api_handler',
                ],
            ],
        ],
        'events' => [
            'livestorm.integration.leadbundle.object_creator' => [
                'class'     => \MauticPlugin\LiveStormBundle\EventListener\LiveStormRelationshipProcessor::class,
                'arguments' => [
                    'livestorm.service.sync.contact_api_handler',
                    'livestorm.service.sync.object_processor',
                ],
            ],
            'livestorm.integration.attendance.points' => [
                'class'     => \MauticPlugin\LiveStormBundle\EventListener\WebinarAttendancePointsSubscriber::class,
                'arguments' => [
                    'mautic.point.model.point',
                ],
            ],
            'livestorm.integration.attendance.interactions' => [
                'class'     => \MauticPlugin\LiveStormBundle\EventListener\WebinarInteractionPointsSubscriber::class,
                'arguments' => [
                    'mautic.point.model.point',
                ],
            ],
            'livestorm.integration.leadbundle.segment_subscriber' => [
                'class'     => \MauticPlugin\LiveStormBundle\EventListener\SegmentEventFiltersSubscriber::class,
                'arguments' => [
                    'livestorm.integration.config',
                    'translator',
                    'livestorm.service.sync.contact_api_handler',
                ],
            ],
            'livestorm.integration.leadbundle.dictionary_subscriber' => [
                'class'     => \MauticPlugin\LiveStormBundle\EventListener\SegmentFiltersDictionarySubscriber::class,
                'arguments' => [
                    'livestorm.integration.config',
                ],
            ],
        ],
        'services' => [
            'livestorm.service.sync.contact_api_handler' => [
                'class'     => \MauticPlugin\LiveStormBundle\Services\LiveStormApiHandler::class,
                'arguments' => [
                    'livestorm.connection.client.api_consumer',
                ],
            ],
            'livestorm.service.sync.object_processor' => [
                'class'     => \MauticPlugin\LiveStormBundle\Services\SyncObjectProcessor::class,
                'arguments' => [
                    'mautic.integration.livestorm.sync.object.mapping',
                    'mautic.lead.model.lead',
                    'mautic.point.model.point',
                    'mautic.integrations.repository.object_mapping',
                ],
            ],
            'mautic.integration.livestorm.sync.object.mapping' => [
                'class'     => \MauticPlugin\LiveStormBundle\Services\SyncObjectMapping::class,
                'arguments' => [
                    'database_connection',
                ],
            ],
        ],
    ],
];
