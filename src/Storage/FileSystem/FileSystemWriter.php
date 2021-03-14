<?php

namespace Press\Engine\Storage\FileSystem;

/**
 * Class FileSystemWriter
 * @package Press\Engine\Storage\FileSystem
 */
class FileSystemWriter
{
    /**
     * FileSystemWritter constructor.
     * @param string $fileName
     */
    public function __construct(private string $fileName)
    {
    }

    /**
     * @param string $text
     */
    public function write(string $text): void
    {
        $this->save($text);
    }

    /**
     * @param string $text
     */
    public function writeln(string $text): void
    {
        $this->save("{$text}\n");
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

    /**
     * @param string $text
     */
    protected function save(string $text): void
    {
        file_put_contents($this->fileName, $text, FILE_APPEND | LOCK_EX);
    }

    /**
     * @return string
     */
    protected function getFileName(): string
    {
        return $this->fileName;
    }
}
