<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $shared = [
            'auth' => [
                'user' => $request->user(),
            ],
            'ziggy' => $this->getZiggyConfig(),
            'flash' => [
                'success' => $request->session()->get('success'),
                'error' => $request->session()->get('error'),
            ],
        ];

        return array_merge(parent::share($request), $shared);
    }

    /**
     * Get Ziggy configuration safely.
     */
    private function getZiggyConfig(): ?array
    {
        $ziggyClass = 'Tighten\Ziggy\Ziggy';

        if (!class_exists($ziggyClass)) {
            return null;
        }

        try {
            return (new $ziggyClass)->toArray();
        } catch (\Throwable) {
            return null;
        }
    }
}
