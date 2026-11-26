<?php

namespace App\Http\Middleware;

use App\Models\Category;
use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user('web')?->only('id', 'name', 'email', 'is_admin'),
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
            ],
            'navCategories' => fn () => Category::query()
                ->orderBy('id')
                ->get(['id', 'slug', 'nama']),
            'storeWhatsapp' => fn () => SiteSetting::getValue('admin_whatsapp', ''),
        ];
    }
}
