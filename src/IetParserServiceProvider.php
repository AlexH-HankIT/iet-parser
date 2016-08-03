<?php

namespace MrCrankHank\IetParser;

use Illuminate\Support\ServiceProvider;
use MrCrankHank\IetParser\Parser\Normalizer;
use MrCrankHank\IetParser\Console\Normalize;
use MrCrankHank\IetParser\Parser\GlobalOptionParser;;

class IetParserServiceProvider extends ServiceProvider
{
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
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(Normalizer::class, function($app, $parameters) {
            return new Normalizer($parameters['parser']);
        });

        $this->app->bind(GlobalOptionParser::class, function($app, $parameters) {
            return new GlobalOptionParser($parameters['filesystem'], $parameters['filePath']);
        });

        $this->commands($this->commands);
    }
}
