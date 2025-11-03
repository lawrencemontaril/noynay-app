<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Illuminate\Foundation\Inspiring;
use Illuminate\Http\Request;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;
use App\Http\Resources\DateTimeResource;
class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        // [$message, $author] = str(Inspiring::quotes()->random())->explode('-');
        $user = $request->user();

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            // 'quote' => ['message' => trim($message), 'author' => trim($author)],
            'auth' => [
                'user' => $user ? [
                    ...$user->toResource()->toArray($request),
                    'permissions' => $user->getAllPermissions()->pluck('name'),
                    'notifications' => $user
                        ? $user
                            ->notifications()
                            ->latest()
                            ->take(10)
                            ->get()
                            ->map(function ($notification) {
                                return [
                                    'id' => $notification->id,
                                    'message' => $notification->data['message'] ?? '',
                                    'link' => $notification->data['link'] ?? '#',
                                    'created_at' => DateTimeResource::make($notification->created_at),
                                    'read_at' => $notification->read_at ? DateTimeResource::make($notification->read_at) : null,
                                ];
                            })
                            ->toArray()
                        : [],
                ] : null,
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'info' => fn () => $request->session()->get('info'),
            ],
            'ziggy' => [
                ...(new Ziggy)->toArray(),
                'location' => $request->url(),
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
        ];
    }
}
