<?php

namespace Press\Engine\Common\Command;

use Press\Engine\Adapter\CommandLineAdapter\Aggregator\Input;
use Press\Engine\Interfaces\Command\CommandInterface;
use Press\Engine\Logger\Logger;

/**
 * Class CommandAbstract
 * @package Press\Engine\Common\Command
 */
abstract class CommandAbstract implements CommandInterface
{
    /**
     * CommandAbstract constructor.
     * @param Logger $logger
     */
    public function __construct(private Logger $logger)
    {
    }

    /**
     * @return array[]
     */
    abstract public static function getArguments(): array;

    /**
     * @return array[]
     */
    abstract public static function getOptions(): array;

    /**
     * @param Input $input
     */
    public function test(Input $input): void
    {
    }

    /**
     * @param int $index
     * @return bool
     */
    public static function hasArgument(int $index): bool
    {
        $args = static::getArguments();
        $keys = array_keys($args);

        return isset($keys[$index]);
    }

    /**
     * @param int $index
     * @return array
     */
    public static function getArgument(int $index): array
    {
        $args = static::getArguments();
        $key = array_keys($args)[$index];

        return [$key, $args[$key]];
    }

    /**
     * @param string $key
     * @return bool
     */
    public static function hasOption(string $key): bool
    {
        return isset(static::getOptions()[$key]);
    }

    /**
     * @param string $key
     * @return array
     */
    public static function getOption(string $key): array
    {
        return [$key, static::getOptions()[$key]];
    }

    /**
     * @return Logger
     */
    protected function getLogger(): Logger
    {
        return $this->logger;
    }
}
