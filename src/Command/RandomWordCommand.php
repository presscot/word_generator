<?php

namespace Press\Engine\Command;

use Press\BusinessLogic\RandomWord\Exception\NoMoreCombinationsException;
use Press\BusinessLogic\RandomWord\Generator;
use Press\Engine\Adapter\CommandLineAdapter\Aggregator\{Input, Output};
use Press\Engine\AppHandler;
use Press\Engine\Common\Command\TimeBlockedCommandAbstract;
use Press\Engine\Exception\BadRequestException;
use Press\Engine\Storage\FileSystem\FileSystemWriter;

/**
 * Class RandomWordCommand
 * @package Press\Engine\Command
 */
class RandomWordCommand extends TimeBlockedCommandAbstract
{
    /**
     * @return string
     */
    public static function getName(): string
    {
        return 'word:generate';
    }

    /**
     * @return array[]
     */
    public static function getArguments(): array
    {
        return [
            'consonants' => [
                'default' => 'aeiou',
                'required' => false,
                'type' => 'string'
            ],
            'vowels' => [
                'default' => 'bcdfghjklmnpqrstvwxyz',
                'required' => false,
                'type' => 'string'
            ]
        ];
    }

    /**
     * @return array[]
     */
    public static function getOptions(): array
    {
        return [
            'count' => [
                'empty' => false,
                'default' => 10,
                'type' => 'int'
            ],
            'min' => [
                'empty' => false,
                'default' => 5,
                'type' => 'int'
            ],
            'max' => [
                'empty' => false,
                'default' => 5,
                'type' => 'int'
            ],
            'force' => [
                'empty' => true,
                'default' => false,
                'type' => 'bool'
            ],
            'notBalanced' => [
                'empty' => true,
                'default' => true,
                'type' => 'bool'
            ]
        ];
    }

    /**
     * @return string
     */
    public static function closedFrom(): string
    {
        return 'Friday 15:00';
    }

    /**
     * @return string
     */
    public static function closedTo(): string
    {
        return 'Monday 10:00';
    }

    /**
     * @param Input $input
     * @throws \Press\Engine\Exception\ParameterNotExistException
     */
    public function test(Input $input): void
    {
        if (2 > (int)$input->get('min')) {
            throw new BadRequestException("The word should be at least two letters long");
        }

        if (!$input->get('force')) {
            parent::test($input);
        }
    }

    /**
     * @param Input $input
     * @param Output $output
     * @throws \Press\Engine\Exception\ParameterNotExistException
     */
    public function run(Input $input, Output $output): void
    {
        $wordsFile = AppHandler::getProjectPatch() . '/data/words.txt';
        $logger = $this->getLogger();
        $fileSystem = new FileSystemWriter($wordsFile);
        $min = 0;
        $max = 0;
        $count = 0;
        $lenArray = [];

        $generator = new Generator(
            $input->get('min'),
            $input->get('max'),
            $input->get('count'),
            $input->get('notBalanced'),
            $input->get('consonants'),
            $input->get('vowels')
        );

        try {
            foreach ($generator->generate() as $word) {
                ++$count;
                $output->writeln($word);
                $fileSystem->writeln($word);
                $lenArray[] = strlen($word);
            }
            $this->addBeam($fileSystem);
        } catch (NoMoreCombinationsException $e) {
            $logger->notice($e->getMessage());
            $output->writeln($e->getMessage());
        }

        if (!empty($lenArray)) {
            $min = min($lenArray);
            $max = max($lenArray);
        }

        $successMessage = "min: {$min}, max: {$max}, count: {$count}";
        $output->writeln($successMessage);
        $logger->log('generator', $successMessage);
    }

    /**
     * @param FileSystemWriter $fileSystem
     */
    private function addBeam(FileSystemWriter $fileSystem): void
    {
        for ($i = 0; $i < 80; ++$i) {
            $fileSystem->write('#');
        }
        $fileSystem->writeln('');
    }
}
