<?php

namespace UniondrugCache;

use Phalcon\Cache\Frontend\Data;
use Phalcon\Cache\Frontend\Igbinary;
use Phalcon\Cache\Frontend\Msgpack;
use Phalcon\Config;
use Phalcon\Di\ServiceProviderInterface;
use Phalcon\Text;

class CacheServiceProvider implements ServiceProviderInterface
{
    public function register(\Phalcon\DiInterface $di)
    {
        $di->set(
            'cache',
            function ($lifetime = null) {
                if ($lifetime === null) {
                    $frontOption = ['lifetime' => (int) $this->getConfig()->path('cache.lifetime', 3600)];
                } else {
                    $frontOption = ['lifetime' => (int) $lifetime];
                }
                if (extension_loaded('igbinary')) {
                    $frontCache = new Igbinary($frontOption);
                } elseif (extension_loaded('msgpack')) {
                    $frontCache = new Msgpack($frontOption);
                } else {
                    $frontCache = new Data($frontOption);
                }

                $adapter = Text::camelize($this->getConfig()->path('cache.adapter', 'file'));
                if (!in_array($adapter, ['File', 'Redis', 'Xcache', 'Mongo', 'Memory', 'Memcache', 'Libmemcached', 'Apc', 'Apcu'])) {
                    throw new \RuntimeException("Invalid cache adapter. $adapter not supported.");
                }
                $options = $this->getConfig()->path('cache.options');
                if (!$options instanceof Config) {
                    throw new \RuntimeException("Cache option cannot be empty");
                }
                $cacheClass = "Phalcon\\Cache\\Backend\\" . $adapter;

                return new $cacheClass($frontCache, $options->toArray());
            }
        );
    }
}
