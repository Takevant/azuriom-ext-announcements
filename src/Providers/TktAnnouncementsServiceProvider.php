<?php

namespace Azuriom\Plugin\TktAnnouncements\Providers;

use Azuriom\Extensions\Plugin\BasePluginServiceProvider;
use Azuriom\Models\ActionLog;
use Azuriom\Models\Permission;
use Azuriom\Plugin\TktAnnouncements\Middleware\InjectAnnouncements;
use Azuriom\Plugin\TktAnnouncements\Models\Announcement;

class TktAnnouncementsServiceProvider extends BasePluginServiceProvider
{
    /**
     * The plugin's global HTTP middleware stack.
     */
    protected array $middleware = [
        InjectAnnouncements::class,
    ];

    /**
     * Register any plugin services.
     */
    public function register(): void
    {
        $this->registerMiddleware();
    }

    /**
     * Bootstrap any plugin services.
     */
    public function boot(): void
    {
        $this->loadViews();

        $this->loadTranslations();

        $this->loadMigrations();

        $this->registerAdminNavigation();

        Permission::registerPermissions([
            'tkt-announcements.admin' => 'tkt-announcements::admin.permission',
        ]);

        ActionLog::registerLogModels([
            Announcement::class,
        ], 'tkt-announcements::admin.logs');

        ActionLog::registerLogs('tkt-announcements.settings.updated', [
            'icon' => 'megaphone',
            'color' => 'info',
            'message' => 'tkt-announcements::admin.logs.settings',
        ]);
    }

    /**
     * Return the admin navigations routes to register in the dashboard.
     *
     * @return array<string, array<string, string>>
     */
    protected function adminNavigation(): array
    {
        return [
            'tkt-announcements' => [
                'name' => trans('tkt-announcements::admin.nav.title'),
                'type' => 'dropdown',
                'icon' => 'bi bi-megaphone',
                'route' => 'tkt-announcements.admin.*',
                'permission' => 'tkt-announcements.admin',
                'items' => [
                    'tkt-announcements.admin.announcements.index' => trans('tkt-announcements::admin.nav.announcements'),
                    'tkt-announcements.admin.settings' => trans('tkt-announcements::admin.nav.settings'),
                ],
            ],
        ];
    }
}
