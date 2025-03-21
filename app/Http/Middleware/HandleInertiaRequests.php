<?php

namespace App\Http\Middleware;

use Auth;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Defines the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function share(Request $request): array
    {
        $auth_token = $request->session()->get('auth_token'); 
         
        if (!$auth_token && Auth::user()) {
            $auth_token = Auth::user()->createToken('myAppToken')->plainTextToken;
            session()->put('auth_token', $auth_token);
        }

        if (auth()->check()) {
            $User = auth()->user();
            // if ($User->image) {
            //     $upload = Upload::where('id', $User->image)->first();
            //     if ($upload) {
            //         $User->image = $upload->file_name ? get_storage_url($upload->file_name) : '';
            //     }
            // }
        }

        return array_merge(parent::share($request), [
            'auth_token' => $auth_token,
            'user'             => $User ?? auth()->user(),
        ]);

        
    }
}
