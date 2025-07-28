<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

// Domain Repos Interfaces
use Domain\Administrator\Repositories\AdministratorRepositoryInterface;
use Domain\Profile\Repositories\ProfileRepositoryInterface;
use Domain\Comment\Repositories\CommentRepositoryInterface;

// Infra Repos
use Infrastructure\Repositories\EloquentAdministratorRepository;
use Infrastructure\Repositories\EloquentProfileRepository;
use Infrastructure\Repositories\EloquentCommentRepository;

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
