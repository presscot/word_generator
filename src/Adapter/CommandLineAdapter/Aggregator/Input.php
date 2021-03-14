<?php

namespace Press\Engine\Adapter\CommandLineAdapter\Aggregator;

use Press\Engine\Exception\ParameterNotExistException;

/**
 * Class Input
 * @package Press\Engine\Adapter\CommandLineAdapter\Aggregator
 */
class Input
{
    /**
     * Input constructor.
     * @param array $source
     */
    public function __construct( private array $source = []){}

    /**
     * @param string $key
     * @param int|float|bool|string $value
     */
    public function add( string $key, int|float|bool|string $value ): void{
        $this->source[$key] = $value;
    }

    /**
     * @param string $key
     * @param int|float|bool|string|null $default
     * @return int|float|bool|string
     * @throws ParameterNotExistException
     */
    public function get( string $key, int|float|bool|string $default = null): int|float|bool|string{
        if( !isset( $this->source[$key] ) ){
            if( null === $default ){
                throw new ParameterNotExistException($key);
            }

            return $default;
        }

        return $this->source[$key];
    }
}
