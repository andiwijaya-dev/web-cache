<?php

namespace Andiwijaya\WebCache;


use Andiwijaya\WebCache\Console\Commands\WebCacheClear;
use Andiwijaya\WebCache\Facades\WebCache;
use Andiwijaya\WebCache\Http\Middleware\WebCacheMiddleware;
use Andiwijaya\WebCache\Http\Middleware\WebCacheMiddlewareDisabled;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Jenssegers\Agent\Agent;

class WebCacheServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {

      $this->app->singleton('WebCache', function () {
        return new WebCacheService();
      });

      $this->commands([
        WebCacheClear::class
      ]);

    }

    public function provides()
    {
      return [ 'WebCache' ];
    }

  /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot(){

      $agent = new Agent();
      $device = $agent->isMobile() ? 'm' : 'd';
      WebCache::key($key = implode('#', [
        $this->app->request->getMethod(),
        $this->app->request->fullUrl(),
        $device,
        $this->app->request->ajax()
      ]));

      $this->app->terminating(function(){
        if($this->app->request->getMethod() == 'GET'){
          global $response;
          WebCache::store($this->app->request, $response);
        }
      });

      if(!$this->app->runningInConsole() && Cache::has($key)){
        global $kernel, $request;
        $response = Response::create(Cache::get($key));
        $response->send();
        $kernel->terminate($request, $response);
        Log::info("Load from cache: " . $request->fullUrl());
        exit();
      }

      $this->app['router']->aliasMiddleware('web-cache', WebCacheMiddleware::class);
      $this->app['router']->aliasMiddleware('web-cache-disabled', WebCacheMiddlewareDisabled::class);

      $this->publishes([__DIR__ . '/2019_12_25_062304_create_web_cache_table.php' =>
        base_path('database/migrations/2019_12_25_062304_create_web_cache_table.php')]);

    }

}