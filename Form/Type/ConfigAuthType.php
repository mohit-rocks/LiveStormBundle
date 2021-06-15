<?php

declare(strict_types=1);

namespace MauticPlugin\LiveStormBundle\Form\Type;

use MauticPlugin\LiveStormBundle\Connection\Client;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class ConfigAuthType extends AbstractType
{
    private $client;

    /**
     * ConfigAuthType constructor.
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'secret',
            TextType::class,
            [
                'label'      => 'livestorm.form.secret',
                'label_attr' => ['class' => 'control-label'],
                'required'   => true,
                'attr'       => [
                    'class' => 'form-control',
                ],
                'constraints' => [
                    new NotBlank(['message' => 'livestorm.form.secret.required']),
                ],
            ]
        );

        $builder->add(
            'host',
            TextType::class,
            [
                'label'      => 'livestorm.form.host',
                'label_attr' => ['class' => 'control-label'],
                'required'   => true,
                'attr'       => [
                    'class' => 'form-control',
                ],
                'constraints' => [
                    new NotBlank(['message' => 'livestorm.form.host.required']),
                ],
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $optionsResolver): void
    {
        $optionsResolver->setDefaults(
            [
                'integration' => null,
                'constraints' => [
                    new Callback([$this, 'validate']),
                ],
            ]
        );
    }

    /**
     * Validate the credentials.
     *
     * @param array $data
     *                    Data coming from the form input
     */
    public function validate(array $data, ExecutionContextInterface $context): void
    {
        if (!empty($data['secret'])) {
            if (!$this->client->validateCredentials($data['host'], $data['secret'])) {
                $context->buildViolation('The API user credentials supplied are invalid.')
                    ->addViolation();
            }
        }
    }
}
