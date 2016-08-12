<?php

/**
 * This file contains the IetParserServiceProvider class
 * It makes the functionality available to laravel
 *
 * PHP version 5.6
 *
 * @category Parser
 * @package  MrCrankHank\IetParser
 * @author   Alexander Hank <mail@alexander-hank.de>
 * @license  Apache License 2.0 http://www.apache.org/licenses/LICENSE-2.0.txt
 * @link     null
 */

namespace MrCrankHank\IetParser;

use Illuminate\Support\ServiceProvider;
use MrCrankHank\IetParser\Parser\Normalizer;
use MrCrankHank\IetParser\Console\Normalize;
use MrCrankHank\IetParser\Parser\GlobalOptionParser;;

/**
 * Class IetParserServiceProvider
 *
 * @category Parser
 * @package  MrCrankHank\IetParser
 * @author   Alexander Hank <mail@alexander-hank.de>
 * @license  Apache License 2.0 http://www.apache.org/licenses/LICENSE-2.0.txt
 * @link     null
 */
class IetParserServiceProvider extends ServiceProvider
{
    /**
     * All commands in this array will be registered with laravel
     *
     * @var array
     */
    protected $commands = [
        Normalize::class
    ];

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(
            GlobalOptionParser::class, function ($app, $parameters) {
                return new GlobalOptionParser(
                    $parameters['filesystem'],
                    $parameters['filePath']
                );
            }
        );

        $this->commands($this->commands);
    }
}
