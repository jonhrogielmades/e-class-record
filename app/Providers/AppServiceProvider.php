<?php

namespace App\Providers;

use App\Database\Migrations\PathSafeMigrator;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\Migrations\Migrator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Fix migration discovery for project paths that contain brackets (e.g. [Laravel]).
        $this->app->extend('migrator', function (Migrator $migrator, $app) {
            $replacement = new PathSafeMigrator(
                $app['migration.repository'],
                $app['db'],
                $app['files'],
                $app[Dispatcher::class],
            );

            $replacement->setConnection($migrator->getConnection());

            foreach ($migrator->paths() as $path) {
                $replacement->path($path);
            }

            return $replacement;
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
