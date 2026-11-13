<?php

namespace Rft\LaravelAttachment;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Rft\LaravelAttachment\View\Components\AttachmentWidgetComponent;

class LaravelAttachmentServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/laravel-attachment.php',
            'laravel-attachment'
        );

        $this->app->singleton(AttachmentManager::class, function () {
            return new AttachmentManager();
        });

        $this->app->alias(AttachmentManager::class, 'laravel-attachment');
    }

    /**
     * Bootstrap any package services.
     */
    public function boot(): void
    {
        $this->ensurePrivateDiskConfigured();

        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'laravel-attachment');

        Blade::component('rft-attachment', AttachmentWidgetComponent::class);
        Blade::component('attachment-widget', AttachmentWidgetComponent::class);

        Blade::directive('attachmentWidget', function (string $expression) {
            return "<?php echo \\Rft\\LaravelAttachment\\Widgets\\AttachmentWidget::widget({$expression}); ?>";
        });

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/laravel-attachment.php' => config_path('laravel-attachment.php'),
            ], 'laravel-attachment-config');

            $this->publishes([
                __DIR__ . '/../database/migrations' => database_path('migrations'),
            ], 'laravel-attachment-migrations');

            $this->publishes([
                __DIR__ . '/../resources/views' => resource_path('views/vendor/laravel-attachment'),
            ], 'laravel-attachment-views');
        }
    }

    /**
     * Ensure the 'private' disk rooted at storage/app/private is available.
     */
    protected function ensurePrivateDiskConfigured(): void
    {
        $diskName = config('laravel-attachment.disk', 'private');
        $rootPath = config('laravel-attachment.storage_root', storage_path('app/private'));

        if (! is_dir($rootPath)) {
            @mkdir($rootPath, 0755, true);
        }

        if (! config()->has("filesystems.disks.{$diskName}")) {
            config()->set("filesystems.disks.{$diskName}", [
                'driver' => 'local',
                'root' => $rootPath,
                'visibility' => 'private',
                'throw' => false,
            ]);
        }
    }
}
