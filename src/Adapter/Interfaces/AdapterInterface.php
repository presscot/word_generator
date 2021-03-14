<?php

namespace Press\Engine\Adapter\Interfaces;

use Press\Engine\Exception\CommandNotFoundException;
use Press\Engine\Interfaces\Command\CommandInterface;
use Press\Engine\Storage\FileSystem\Metadata;

interface AdapterInterface
{
    /**
     * @param string $text
     */
    public function stream(string $text): void;

    /**
     * @return string
     */
    public function getProviderName(): string;

    /**
     * @param Metadata $provider
     * @throws \ReflectionException
     */
    public function validatePayload(Metadata $provider): void;
    /**
     * @param Metadata $provider
     * @throws \ReflectionException
     */
    public function dispatch(Metadata $provider): void;

    /**
     * @return Metadata
     * @throws CommandNotFoundException
     */
    public function resolve(): Metadata;
}
