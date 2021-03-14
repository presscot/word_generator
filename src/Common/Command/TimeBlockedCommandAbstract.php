<?php

namespace Press\Engine\Common\Command;

use Press\Engine\Adapter\CommandLineAdapter\Aggregator\Input;
use DateTime;
use Press\Engine\Exception\BadConfigurationException;
use Press\Engine\Exception\UsageLockException;

/**
 * Class TimeBlockedCommandAbstract
 * @package Press\Engine\Common\Command
 */
abstract class TimeBlockedCommandAbstract extends CommandAbstract
{
    /**
     * @return string
     */
    abstract public static function closedFrom(): string;

    /**
     * @return string
     */
    abstract public static function closedTo(): string;

    /**
     * @param Input $input
     */
    public function test(Input $input): void
    {
        parent::test($input);

        $from = $this->getStartDay(static::closedFrom());
        $to = $this->getEndDay($from, static::closedTo());

        if (!$this->validateTimeRange($from, $to)) {
            $message = '';
            $message .= "The use of this command is available between ";
            $message .= "{$from->format('l H:i')} and {$to->format('l H:i')}";
            throw new UsageLockException($message);
        }
    }

    /**
     * @param DateTime $from
     * @param DateTime $to
     * @return bool
     */
    protected function validateTimeRange(DateTime $from, DateTime $to): bool
    {
        $now = new DateTime('now');

        return !($now >= $from && $now <= $to);
    }

    /**
     * @param string $time
     * @return DateTime
     * @throws BadConfigurationException
     */
    protected function getStartDay(string $time): DateTime
    {
        [$day, $hours, $minutes] = $this->parseTime($time);

        $start = new DateTime('now');
        if ($day !== $start->format('L')) {
            $start->modify("previous {$day}");
        }

        return $start->setTime($hours, $minutes, 0);
    }

    /**
     * @param DateTime $start
     * @param string $time
     * @return DateTime
     * @throws BadConfigurationException
     */
    protected function getEndDay(DateTime $start, string $time): DateTime
    {
        [$day, $hours, $minutes] = $this->parseTime($time);

        $end = clone $start;

        if ($day !== $end->format('L')) {
            $end->modify("next {$day}");
        }

        return $end->setTime($hours, $minutes, 0);
    }

    /**
     * @param string $time
     * @return array
     * @throws BadConfigurationException
     */
    protected function parseTime(string $time): array
    {
        $pattern = '/^(?P<day>monday|tuesday|wednesday|thursday|friday|saturday|sunday)\s+(?P<hours>\d{1,2})\:(?P<minutes>\d{1,2})$/mi';

        if (1 !== preg_match($pattern, $time, $m)) {
            throw new BadConfigurationException("");
        }

        return [$m['day'], (int)$m['hours'], (int)$m['minutes']];
    }
}
