<?php

namespace Press\Engine\Adapter\CommandLineAdapter\Aggregator;

use Press\Engine\Storage\FileSystem\FileSystemWriter;

/**
 * Class Output
 * @package Press\Engine\Adapter\CommandLineAdapter\Aggregator
 */
class Output
{
    /**
     * @param string $text
     */
    public function write(string $text): void
    {
        echo $text;
    }

    /**
     * @param string $text
     */
    public function writeln(string $text): void
    {
        echo "$text\n";
    }

    /**
     * @param string[] $texts
     */
    public function writelnArray(array $texts): void
    {
        foreach ($texts as $text) {
            $this->writeln($text);
        }
    }
}
