<?php

declare(strict_types=1);

namespace App\Orchid;

use Orchid\Platform\Dashboard;
use Orchid\Platform\ItemPermission;
use Orchid\Platform\OrchidServiceProvider;
use Orchid\Screen\Actions\Menu;
use Orchid\Support\Color;

class PlatformProvider extends OrchidServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @param Dashboard $dashboard
     *
     * @return void
     */
    public function boot(Dashboard $dashboard): void
    {
        parent::boot($dashboard);

        // ...
    }

    /**
     * Register the application menu.
     *
     * @return Menu[]
     */
    public function menu(): array
    {
        return [
            Menu::make('Get Started')
                ->icon('bs.book')
                ->title('Navigation')
                ->route(config('platform.index')),

            Menu::make('Sample Screen')
                ->icon('bs.collection')
                ->route('platform.example')
                ->badge(fn () => 6),

            Menu::make('Form Elements')
                ->icon('bs.card-list')
                ->route('platform.example.fields')
                ->active('*/examples/form/*'),

            Menu::make('Layouts Overview')
                ->icon('bs.window-sidebar')
                ->route('platform.example.layouts'),

            Menu::make('Grid System')
                ->icon('bs.columns-gap')
                ->route('platform.example.grid'),

            Menu::make('Charts')
                ->icon('bs.bar-chart')
                ->route('platform.example.charts'),

            Menu::make('Cards')
                ->icon('bs.card-text')
                ->route('platform.example.cards')
                ->divider(),

            Menu::make(__('Users'))
                ->icon('bs.people')
                ->route('platform.systems.users')
                ->permission('platform.systems.users')
                ->title(__('Access Controls')),

            Menu::make(__('Roles'))
                ->icon('bs.shield')
                ->route('platform.systems.roles')
                ->permission('platform.systems.roles')
                ->divider(),

            Menu::make('Usaha Saya')
                ->icon('bs.shop')
                ->route('platform.my_businesses')
                ->title('Menu Anggota'),

            Menu::make('Peluang Saya')
                ->icon('bs.lightbulb')
                ->route('platform.my_opportunities'),

            Menu::make('Referral Saya')
                ->icon('bs.share')
                ->route('platform.my_referrals'),

            Menu::make('Event Saya')
                ->icon('bs.calendar-event')
                ->route('platform.my_events'),

            Menu::make('Semua Usaha')
                ->icon('bs.buildings')
                ->route('platform.businesses')
                ->permission('platform.businesses')
                ->title('Kelola Komunitas'),

            Menu::make('Semua Peluang')
                ->icon('bs.megaphone')
                ->route('platform.opportunities')
                ->permission('platform.opportunities'),

            Menu::make('Semua Referral')
                ->icon('bs.diagram-3')
                ->route('platform.referrals')
                ->permission('platform.referrals'),

            Menu::make('Semua Event')
                ->icon('bs.calendar-check')
                ->route('platform.events')
                ->permission('platform.events'),

            Menu::make('Peran Profesi')
                ->icon('bs.briefcase')
                ->route('platform.master.professional_roles')
                ->permission('platform.master.professional_roles')
                ->title('Master Data'),

            Menu::make('Skill & Layanan')
                ->icon('bs.gear')
                ->route('platform.master.skills')
                ->permission('platform.master.skills'),

            Menu::make('Kategori Usaha')
                ->icon('bs.tags')
                ->route('platform.master.business_categories')
                ->permission('platform.master.business_categories')
                ->divider(),

            Menu::make('Documentation')
                ->title('Docs')
                ->icon('bs.box-arrow-up-right')
                ->url('https://orchid.software/en/docs')
                ->target('_blank'),

            Menu::make('Changelog')
                ->icon('bs.box-arrow-up-right')
                ->url('https://github.com/orchidsoftware/platform/blob/master/CHANGELOG.md')
                ->target('_blank')
                ->badge(fn () => Dashboard::version(), Color::DARK),
        ];
    }

    /**
     * Register permissions for the application.
     *
     * @return ItemPermission[]
     */
    public function permissions(): array
    {
        return [
            ItemPermission::group(__('System'))
                ->addPermission('platform.systems.roles', __('Roles'))
                ->addPermission('platform.systems.users', __('Users')),

            ItemPermission::group('Kelola Data')
                ->addPermission('platform.businesses', 'Daftar Semua Usaha')
                ->addPermission('platform.opportunities', 'Daftar Semua Peluang')
                ->addPermission('platform.referrals', 'Daftar Semua Referral')
                ->addPermission('platform.events', 'Daftar Semua Event'),

            ItemPermission::group('Master Data')
                ->addPermission('platform.master.professional_roles', 'Peran Profesi')
                ->addPermission('platform.master.skills', 'Skill & Layanan')
                ->addPermission('platform.master.business_categories', 'Kategori Usaha'),
        ];
    }
}
