<?php

namespace Press\Engine\Storage\FileSystem;

use FilterIterator;
use FilesystemIterator;
use Press\Engine\AppHandler;
use SplFileInfo;
use ReflectionClass;

/**
 * Class FileSystemReader
 * @package Press\Engine\Storage\FileSystem
 */
class FileSystemReader extends FilterIterator
{
    /**
     * FileSystemReader constructor.
     * @param string $path
     */
    public function __construct(private string $path)
    {
        parent::__construct(new FilesystemIterator($path));
    }

    /**
     * @return bool
     */
    public function accept(): bool
    {
        $item = $this->getInnerIterator()->current();
        return $item->isFile() && 'php' === $item->getExtension();
    }

    /**
     * @param string $suffix
     * @param string $interface
     * @return array
     */
    public function getContents(string $suffix = '', string $interface = ''): array
    {
        return array_map(
            fn(SplFileInfo $el) => $this->getNamespace($el),
            iterator_to_array(new class ($this, $suffix, $interface) extends FilterIterator {

                public function __construct(
                    private FileSystemReader $iterator,
                    private string $suffix,
                    private string $interface
                )
                {
                    parent::__construct($iterator);
                }

                public function accept(): bool
                {
                    $item = $this->getInnerIterator()->current();
                    $namespace = $this->iterator->getNamespace($item);

                    return
                        (
                            '' === $this->suffix
                            || str_ends_with($namespace, $this->suffix)
                        )
                        && (
                            '' === $this->interface
                            || (
                                ($rc = new ReflectionClass($namespace))
                                && $rc->implementsInterface($this->interface)
                            )
                        );
                }
            }, false)
        );
    }

    /**
     * @param SplFileInfo $item
     * @return string
     */
    public function getNamespace(SplFileInfo $item): string
    {
        $fileName = $item->getBaseName('.php');
        return AppHandler::getNamespace("{$item->getPath()}/$fileName");
    }
}
