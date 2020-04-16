<?php

namespace App\Providers;

use App\Repositories\AchievementRepositoryConfig;
use App\Repositories\EventRepositoryConfig;
use App\Repositories\ItemRepositoryDbFactory;
use App\Repositories\PlayerRepository;
use App\Repositories\PlayerRepositoryDb;
use App\Repositories\RecipeRepositoryConfig;
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

        $this->app->singleton(AchievementRepositoryConfig::class, function ($app) {
            return new AchievementRepositoryConfig(
                include __DIR__ . "/../../config/achievements.php"
            );
        });

        $this->app->singleton(EventRepositoryConfig::class, function ($app) {
            return new EventRepositoryConfig(
                include __DIR__ . "/../../config/events.php"
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
