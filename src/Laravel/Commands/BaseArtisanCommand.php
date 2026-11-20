<?php

declare(strict_types=1);

namespace UniversalAgentOS\Laravel\Commands;

if (!class_exists(\Illuminate\Console\Command::class)) {
    abstract class BaseCommandStub
    {
        /** @var string */
        protected $signature = '';
        /** @var string */
        protected $description = '';

        public function argument(?string $key = null): mixed
        {
            return null;
        }

        public function option(?string $key = null): mixed
        {
            return null;
        }

        public function info(string $string): void
        {
            echo $string . PHP_EOL;
        }

        public function error(string $string): void
        {
            echo $string . PHP_EOL;
        }

        public function line(string $string): void
        {
            echo $string . PHP_EOL;
        }
    }
    class_alias(BaseCommandStub::class, \Illuminate\Console\Command::class);
}

/**
 * Base Artisan command integrating UAOS commands into Laravel console lifecycle.
 */
abstract class BaseArtisanCommand extends \Illuminate\Console\Command
{
}
