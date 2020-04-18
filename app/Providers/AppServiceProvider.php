<?php

namespace App\Providers;

use App\Repositories\AchievementRepositoryConfig;
use App\Repositories\EventRepositoryConfig;
use App\Repositories\ItemRepository;
use App\Repositories\ItemRepositoryDb;
use App\Repositories\ItemRepositoryDbFactory;
use App\Repositories\LocationRepositoryConfig;
use App\Repositories\PlayerRepository;
use App\Repositories\PlayerRepositoryDb;
use App\Repositories\RecipeRepositoryConfig;
use App\ViewModels\AchievementFactory;
use App\ViewModels\ContainerFactory;
use App\ViewModels\EventFactory;
use App\ViewModels\ItemFactory;
use App\ViewModels\LocationFactory;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(ItemRepositoryDbFactory::class, function ($app) {
            return new ItemRepositoryDbFactory(
                include __DIR__ . "/../../config/objects.php"
            );
        });

        $this->app->singleton(RecipeRepositoryConfig::class, function ($app) {
            return new RecipeRepositoryConfig(
                include __DIR__ . "/../../config/recipes.php"
            );
        });

        $this->app->singleton(LocationRepositoryConfig::class, function ($app) {
            return new LocationRepositoryConfig(
                include __DIR__ . "/../../config/locations.php"
            );
        });

        $this->app->singleton(LocationFactory::class, function ($app) {
            return new LocationFactory(
                include __DIR__ . "/../../config/locations.php",
                $app[LocationRepositoryConfig::class],
                $app[ItemFactory::class]
            );
        });

        $this->app->singleton(ItemFactory::class, function ($app) {
            return new ItemFactory(
                include __DIR__ . "/../../config/objects.php"
            );
        });

        $this->app->singleton(EventFactory::class, function ($app) {
            return new EventFactory(
                include __DIR__ . "/../../config/events.php",
                $app[LocationFactory::class],
                $app[LocationRepositoryConfig::class]
            );
        });

        $this->app->singleton(AchievementFactory::class, function ($app) {
            return new AchievementFactory(
                include __DIR__ . "/../../config/achievements.php"
            );
        });

        $this->app->singleton(ContainerFactory::class, function ($app) {
            return new ContainerFactory(
                include __DIR__ . "/../../config/objects.php"
            );
        });

        $this->app->bind(PlayerRepository::class, PlayerRepositoryDb::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
