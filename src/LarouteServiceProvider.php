<?php

namespace Gefar\Laroute;

use Illuminate\Support\ServiceProvider;
use Gefar\Laroute\Console\Commands\LarouteGeneratorCommand;
use Gefar\Laroute\Routes\Collection as Routes;

class LarouteServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $source = $this->getConfigPath();
        $this->publishes([$source => config_path('laroute-api.php')], 'config');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $source = $this->getConfigPath();
        $this->mergeConfigFrom($source, 'laroute-api');

        $this->registerGenerator();

        $this->registerCompiler();

        $this->registerCommand();
    }

    /**
     * Get the config path
     *
     * @return string
     */
    protected function getConfigPath()
    {
        return realpath(__DIR__ . '/../config/laroute-api.php');
    }

    /**
     * Register the generator.
     *
     * @return void
     */
    protected function registerGenerator()
    {
        $this->app->bind(
            'Gefar\Laroute\Generators\GeneratorInterface',
            'Gefar\Laroute\Generators\TemplateGenerator'
        );
    }

    /**
     * Register the compiler.
     *
     * @return void
     */
    protected function registerCompiler()
    {
        $this->app->bind(
            'Gefar\Laroute\Compilers\CompilerInterface',
            'Gefar\Laroute\Compilers\TemplateCompiler'
        );
    }

    /**
     * Register the command
     *
     * @return void
     */
    protected function registerCommand()
    {
        $this->app->singleton(
            'command.laroute-api.generate',
            function ($app) {
                $config     = $app['config'];

                $routes     = new Routes(app('Dingo\Api\Routing\Router')->getRoutes($config->get('api.version')), $config->get('laroute.filter', 'all'), $config->get('laroute.action_namespace', ''));

                $generator  = $app->make('Gefar\Laroute\Generators\GeneratorInterface');

                return new LarouteGeneratorCommand($config, $routes, $generator);
            }
        );

        $this->commands('command.laroute.generate');
    }
}
