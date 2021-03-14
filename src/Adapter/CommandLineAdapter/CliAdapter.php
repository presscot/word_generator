<?php

namespace Press\Engine\Adapter\CommandLineAdapter;

use Press\Engine\Adapter\CommandLineAdapter\Aggregator\Output;
use Press\Engine\Exception\CommandNotFoundException;
use Press\Engine\Logger\Logger;
use Press\Engine\Storage\FileSystem\FileSystemReader;
use Press\Engine\Adapter\CommandLineAdapter\Aggregator\InputHydrator;
use Press\Engine\Adapter\Interfaces\AdapterInterface;
use Press\Engine\AppHandler;
use Press\Engine\Storage\FileSystem\Metadata;
use Press\Engine\Interfaces\Command\CommandInterface;

/**
 * Class CliAdapter
 * @package Press\Engine\Adapter\CommandLineAdapter
 */
class CliAdapter implements AdapterInterface
{
    /**
     * CliAdapter constructor.
     * @param InputHydrator $input
     */
    public function __construct(private InputHydrator $input){}

    /**
     * @param string $text
     */
    public function stream(string $text): void{
        echo "{$text}\n";
    }

    /**
     * @return string
     */
    public function getProviderName(): string
    {
        return $this->input->getName();
    }

    /**
     * @param Metadata $provider
     * @throws \ReflectionException
     */
    public function validatePayload(Metadata $provider): void
    {
        $this->input->hydrate($provider);
    }

    /**
     * @param Metadata $provider
     * @throws \ReflectionException
     */
    public function dispatch(Metadata $provider): void
    {
        $logger = new Logger();

        $command = $provider->getReflectionClass()->newInstance($logger);
        $input = $this->input->getInput();

        $command->test( $input );
        $provider->getReflectionMethod()->invoke($command, $input, new Output() );
    }

    /**
     * @return Metadata
     * @throws CommandNotFoundException
     */
    public function resolve(): Metadata
    {
        $providerName = $this->getProviderName();

        /** @var CommandInterface $provider */
        foreach ($this->getAvaiableProviders() as $provider) {
            if ($providerName === $provider::getName()) {
                return new Metadata($provider, 'run');
            }
        }

        throw new CommandNotFoundException($providerName);
    }

    /**
     * @return CommandInterface[]
     */
    protected function getAvaiableProviders(): array
    {
        $src = AppHandler::getSrcPath();
        $src .= '/Command';

        $fileSystemReader = new FileSystemReader($src);

        return $fileSystemReader->getContents('Command', CommandInterface::class);
    }
}
