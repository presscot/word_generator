<?php

namespace Press\Engine\Storage\FileSystem;

use ReflectionClass;
use ReflectionMethod;

/**
 * Class Metadata
 * @package Press\Engine\Storage\FileSystem
 */
class Metadata
{
    private ReflectionClass $rc;
    private ReflectionMethod $rm;

    /**
     * Metadata constructor.
     * @param string $namespace
     * @param string $method
     * @throws \ReflectionException
     */
    public function __construct(private string $namespace, private string $method)
    {
        $this->rc = new ReflectionClass($namespace);

        if (!$this->rc->hasMethod($method)) {
            throw new \Exception();
        }

        $this->rm = $this->rc->getMethod($method);
    }

    /**
     * @return string
     */
    public function getNamespace(): string
    {
        return $this->namespace;
    }

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @return ReflectionClass
     */
    public function getReflectionClass(): ReflectionClass
    {
        return $this->rc;
    }

    /**
     * @return ReflectionMethod
     */
    public function getReflectionMethod(): ReflectionMethod
    {
        return $this->rm;
    }

    /**
     * @return string
     */
    public function getInfo(): string{
        return "{$this->getNamespace()}::{$this->getMethod()}()";
    }
}
