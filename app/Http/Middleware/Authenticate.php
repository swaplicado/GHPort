<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function redirectTo($request)
    {
        $uri = $request->getRequestUri();
        $check = "/";
        if ($uri[0] == $check) {
            $uri = substr_replace($uri, "", 0, 1);
        }
        $uri = preg_split("/\//", $uri);
        
        if (! $request->expectsJson()) {
            if(count($uri) > 1){
                $ruta = route('login', ['idRoute' => $uri[2], 'idApp' => $uri[3]]);
            }else{
                $ruta = route('login', ['idRoute' => $uri[2]]);
            }
            return $ruta;
        }
    }
}
