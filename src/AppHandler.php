<?php

namespace Press\Engine;

use Press\Engine\Adapter\Interfaces\AdapterInterface;
use Press\Engine\Logger\Logger;
use Press\Engine\Storage\FileSystem\Metadata;
use RuntimeException;
use Throwable;

/**
 * Class AppHandler
 * @package Press\Engine
 */
final class AppHandler
{
    /**
     * @param AdapterInterface $adapter
     */
    public function handle(AdapterInterface $adapter): void
    {
        $logger = new Logger();

        try{
            $provider = $this->start($adapter);
        }catch ( RuntimeException $e ){
            $logger->warning($e->getMessage());
            $adapter->stream($e->getMessage());
        }catch ( Throwable $e ){
            $logger->error($e->getMessage());
            $adapter->stream($e->getMessage());
        } finally {
            if( !(@$e instanceof Throwable) ){
                $logger->info($provider->getInfo());
            }
        }
    }

    /**
     * @param AdapterInterface $adapter
     * @return Metadata
     * @throws Exception\CommandNotFoundException
     * @throws \ReflectionException
     */
    protected function start(AdapterInterface $adapter): Metadata{
        $provider = $adapter->resolve();
        $adapter->validatePayload($provider);
        $adapter->dispatch($provider);

        return $provider;
    }

    /**
     * @return string
     */
    public static function getSrcPath(): string
    {
        return __DIR__;
    }

    /**
     * @return string
     */
    public static function getProjectPatch(): string{

        return dirname( self::getSrcPath());
    }

    /**
     * @param string $path
     * @return string
     */
    public static function getNamespace( string $path = '' ): string
    {
        $namespace = __NAMESPACE__;
        $srcPath = self::getSrcPath();
        $srcPath = str_replace('/', '\/', $srcPath);
        $path = preg_replace(["/^{$srcPath}/", '/\//'], ['','\\'], $path);

        return "{$namespace}{$path}";
    }
}
