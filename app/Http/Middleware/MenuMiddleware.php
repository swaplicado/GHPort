<?php

namespace App\Http\Middleware;

use Closure;
use App\Menu\Menu;

class MenuMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $sMenu = Menu::createMenu(\Auth::user());
        session(['menu' => $sMenu]);

        return $next($request);
    }
}