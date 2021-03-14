<?php

namespace Press\Engine\Adapter\CommandLineAdapter\Aggregator;

use Press\Engine\Storage\FileSystem\Metadata;
use Press\Engine\Exception\WarrningException;

/**
 * Class InputHydrator
 * @package Press\Engine\Adapter\CommandLineAdapter\Aggregator
 */
class InputHydrator
{
    private string $name;
    private array $argv;

    private ?Input $input = null;

    /**
     * @return static
     * @throws \Exception
     */
    static function createFromGlobal(): self
    {
        $argv = $_SERVER['argv'];
        $argc = $_SERVER['argc'];

        return new self($argv, $argc);
    }

    /**
     * InputHydrator constructor.
     * @param array $argv
     * @param int $argc
     * @throws \Exception
     */
    public function __construct(array $argv, int $argc)
    {
        $this->grabElement( $argv);

        if (2 > $argc) {
            throw new \Exception();
        }

        $this->name = $this->grabElement( $argv);
        $this->argv = $argv;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $el
     * @param Metadata $provider
     * @return array
     * @throws \ReflectionException
     */
    protected function parseLongOption( string $el, Metadata $provider): array{
        $el = ltrim($el, '-');
        [$key, $value] = explode('=', "{$el}=");
        $rc = $provider->getReflectionClass();

        if( !$rc->getMethod('hasOption')->invoke(null, $key) ){
            throw new WarrningException();
        }

        [ $key, $metadata ] = $rc->getMethod('getOption')->invoke(null, $key);

        $value = $metadata['empty'] ? !$metadata['default'] : $value;

        return [$key, $value];
    }

    /**
     * @param string $el
     * @param Metadata $provider
     * @return array
     * @throws \ReflectionException
     */
    protected function parseShortOption( string $el, Metadata $provider ): array{
        $key = ltrim($el, '-');

        $rc = $provider->getReflectionClass();

        if( !$rc->getMethod('hasOption')->invoke(null, $key) ){
            throw new WarrningException();
        }

        [ $key, $metadata ] = $rc->getMethod('getOption')->invoke(null, $key);

        $value = $metadata['empty'] ? !$metadata['default'] : $el = $this->grabElement($this->argv);

        return [$key, $value];
    }

    /**
     * @param string $value
     * @param Metadata $provider
     * @param int $index
     * @return array
     * @throws \ReflectionException
     */
    protected function parseArgument( string $value, Metadata $provider, int $index ): array{
        $rc = $provider->getReflectionClass();

        if( !$rc->getMethod('hasArgument')->invoke(null, $index) ){
            throw new WarrningException();
        }

        [ $key, $metadata ] = $rc->getMethod('getArgument')->invoke(null, $index);

        return [ $key, $value ];
    }

    /**
     * @param Metadata $provider
     * @return Input
     * @throws \ReflectionException
     */
    public function hydrate(Metadata $provider): Input
    {
        $i = 0;

        $rc = $provider->getReflectionClass();
        $source =
            array_map(
                fn($x) => $x['default'],
                array_merge(
                    $rc->getMethod('getOptions')->invoke(null),
                    $rc->getMethod('getArguments')->invoke(null)
                )
            )
        ;
        $input = new Input($source);

        while( !empty( $this->argv ) ){
            $el = $this->grabElement($this->argv);

            try{
                [$key, $value] = match (true) {
                    str_starts_with($el, '--') => $this->parseLongOption($el,$provider),
                    str_starts_with($el, '-') => $this->parseShortOption($el,$provider),
                    default => $this->parseArgument($el,$provider, $i++)
                };

                $input->add($key, $value);
            }catch( WarrningException $e){

            }
        }

        $this->input = $input;

        return $this->input;
    }

    /**
     * @return Input
     * @throws \Exception
     */
    public function getInput(): Input
    {
        if( !($this->input instanceof Input) ){
            throw new \Exception();
        }

        return $this->input;
    }

    /**
     * @param array $argv
     * @return mixed
     */
    protected function grabElement(array &$argv){
        return array_shift($argv);
    }
}
