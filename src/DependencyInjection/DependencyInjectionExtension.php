<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusAgreementPlugin\DependencyInjection;

use Psr\Container\ContainerInterface;
use Symfony\Component\Form\Exception\InvalidArgumentException;
use Symfony\Component\Form\FormExtensionInterface;
use Symfony\Component\Form\FormTypeExtensionInterface;
use Symfony\Component\Form\FormTypeGuesserChain;
use Symfony\Component\Form\FormTypeGuesserInterface;
use Symfony\Component\Form\FormTypeInterface;

class DependencyInjectionExtension implements FormExtensionInterface
{
    /** @var FormTypeGuesserInterface|null */
    private $guesser;

    /** @var bool */
    private $guesserLoaded = false;

    /** @var ContainerInterface */
    private $typeContainer;

    /** @var iterable[] */
    private $typeExtensionServices;

    /** @var iterable */
    private $guesserServices;

    private array $agreementExtensionTypes;

    /**
     * @param iterable[] $typeExtensionServices
     */
    public function __construct(
        ContainerInterface $typeContainer,
        array $typeExtensionServices,
        iterable $guesserServices,
        array $agreementExtensionTypes,
    ) {
        $this->typeContainer = $typeContainer;
        $this->typeExtensionServices = $typeExtensionServices;
        $this->guesserServices = $guesserServices;
        $this->agreementExtensionTypes = $agreementExtensionTypes;
    }

    /**
     * @inheritdoc
     */
    public function getType($name): FormTypeInterface
    {
        if (!$this->typeContainer->has($name)) {
            throw new InvalidArgumentException(sprintf('The field type "%s" is not registered in the service container.', $name));
        }

        return $this->typeContainer->get($name);
    }

    /**
     * @inheritdoc
     */
    public function hasType($name)
    {
        return $this->typeContainer->has($name);
    }

    /**
     * @inheritdoc
     *
     * @return FormTypeExtensionInterface[]
     */
    public function getTypeExtensions($name)
    {
        /** @var FormTypeExtensionInterface[] $extensions */
        $extensions = [];

        if (isset($this->typeExtensionServices[$name])) {
            foreach ($this->typeExtensionServices[$name] as $extension) {
                $extensions[] = $extension;

                $extendedTypes = $this->loadExtendedTypes($extension);
                // validate the result of getExtendedTypes() to ensure it is consistent with the service definition
                if (!\in_array($name, $extendedTypes, true)) {
                    throw new InvalidArgumentException(sprintf('The extended type "%s" specified for the type extension class "%s" does not match any of the actual extended types (["%s"]).', $name, \get_class($extension), implode('", "', $extendedTypes)));
                }
            }
        }

        return $extensions;
    }

    /**
     * @param FormTypeExtensionInterface $extension
     */
    private function loadExtendedTypes($extension): array
    {
        $extendedTypes = [];
        foreach ($extension::getExtendedTypes() as $extendedType) {
            $extendedTypes[] = $extendedType;
        }

        return array_merge($extendedTypes, $this->agreementExtensionTypes);
    }

    /**
     * @inheritdoc
     */
    public function hasTypeExtensions($name)
    {
        return isset($this->typeExtensionServices[$name]);
    }

    /**
     * @inheritdoc
     */
    public function getTypeGuesser()
    {
        if (!$this->guesserLoaded) {
            $this->guesserLoaded = true;
            $guessers = [];

            foreach ($this->guesserServices as $serviceId => $service) {
                $guessers[] = $service;
            }

            if ([] !== $guessers) {
                $this->guesser = new FormTypeGuesserChain($guessers);
            }
        }

        return $this->guesser;
    }
}
