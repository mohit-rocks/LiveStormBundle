<?php

namespace MauticPlugin\LiveStormBundle\Integration\Support;

use Mautic\IntegrationsBundle\Integration\DefaultConfigFormTrait;
use Mautic\IntegrationsBundle\Integration\Interfaces\ConfigFormAuthInterface;
use Mautic\IntegrationsBundle\Integration\Interfaces\ConfigFormFeatureSettingsInterface;
use Mautic\IntegrationsBundle\Integration\Interfaces\ConfigFormFeaturesInterface;
use Mautic\IntegrationsBundle\Integration\Interfaces\ConfigFormInterface;
use Mautic\IntegrationsBundle\Integration\Interfaces\ConfigFormSyncInterface;
use Mautic\IntegrationsBundle\Sync\SyncDataExchange\Internal\Object\Contact;
use MauticPlugin\LiveStormBundle\Form\Type\ConfigAuthType;
use MauticPlugin\LiveStormBundle\Form\Type\ConfigFeaturesType;
use MauticPlugin\LiveStormBundle\Integration\LiveStormIntegration;
use MauticPlugin\LiveStormBundle\Sync\Mapping\Field\FieldRepository;
use MauticPlugin\LiveStormBundle\Sync\Mapping\Manual\MappingManualFactory;

class ConfigSupport extends LiveStormIntegration implements ConfigFormInterface, ConfigFormAuthInterface, ConfigFormFeatureSettingsInterface, ConfigFormSyncInterface, ConfigFormFeaturesInterface
{
    use DefaultConfigFormTrait;

    /**
     * @var FieldRepository
     */
    private $fieldRepository;

    public function __construct(FieldRepository $fieldRepository)
    {
        $this->fieldRepository = $fieldRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthConfigFormName(): string
    {
        return ConfigAuthType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getFeatureSettingsConfigFormName(): string
    {
        return ConfigFeaturesType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getSupportedFeatures(): array
    {
        return [
            ConfigFormFeaturesInterface::FEATURE_SYNC => 'mautic.integration.feature.sync',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getSyncConfigObjects(): array
    {
        return [
            MappingManualFactory::LIVESTORM_OBJECT => 'livestorm.object.contact',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getSyncMappedObjects(): array
    {
        return [
            MappingManualFactory::LIVESTORM_OBJECT => Contact::NAME,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getRequiredFieldsForMapping(string $object): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getOptionalFieldsForMapping(string $object): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getAllFieldsForMapping(string $object): array
    {
        return $this->fieldRepository->getAllFieldsForMapping($object);
    }
}
