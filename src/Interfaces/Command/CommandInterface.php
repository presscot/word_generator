<?php

namespace Press\Engine\Interfaces\Command;

use Press\Engine\Adapter\CommandLineAdapter\Aggregator\{Input, Output};

/**
 * Interface CommandInterface
 * @package Press\Engine\Interfaces\Command
 */
interface CommandInterface
{
    /**
     * @return string
     */
    public static function getName(): string;

    /**
     * @return array[]
     */
    public static function getArguments(): array;

    /**
     * @return array[]
     */
    public static function getOptions(): array;

    /**
     * @param Input $input
     */
    public function test(Input $input): void;

    /**
     * @param Input $input
     * @param Output $output
     */
    public function run(Input $input, Output $output): void;
}
