<?php

namespace App\Providers;

use Domain\Administrator\Repositories\AdministratorRepositoryInterface;
// Domain Repos Interfaces
use Domain\Comment\Repositories\CommentRepositoryInterface;
use Domain\Profile\Repositories\ProfileRepositoryInterface;
use Illuminate\Support\ServiceProvider;
// Infra Repos
use Infrastructure\Repositories\EloquentAdministratorRepository;
use Infrastructure\Repositories\EloquentCommentRepository;
use Infrastructure\Repositories\EloquentProfileRepository;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Binding des Repos Interfaces avec leurs implémentations Eloquent
        $this->app->bind(
            AdministratorRepositoryInterface::class,
            EloquentAdministratorRepository::class
        );

        $this->app->bind(
            ProfileRepositoryInterface::class,
            EloquentProfileRepository::class
        );

        $this->app->bind(
            CommentRepositoryInterface::class,
            EloquentCommentRepository::class
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
