<?php

namespace Andiwijaya\WebCache;


use Andiwijaya\WebCache\Jobs\WebCacheLoad;
use Andiwijaya\WebCache\Models\WebCache;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class WebCacheService{

  protected $key;
  protected $tags = [];
  protected $dontStore = false;

  public function key($key){

    $this->key = $key;

  }

  public function tag($tag){

    $this->tags[] = $tag;

  }

  public function dontStore(){

    $this->dontStore = true;

  }

  public function store($request, $response){

    if(!$this->dontStore &&
      ($response instanceof Response || $response instanceof JsonResponse)){

      Cache::put($this->key, $response->content());

      if(Schema::hasTable('web_cache')){

        WebCache::updateOrCreate(
          [ 'key'=>$this->key ],
          [
            'tag'=>implode(' ', $this->tags),
            'created_ua'=>$request->header('user-agent'),
            'created_ip'=>$request->ip()
          ]
        );

      }
      else{

        Log::warning("Table 'web_cache' doesn't exists, clear by key featured is not available.");

      }

    }

  }



  public function clearAll(){

    $cleared_keys = [];

    if(Schema::hasTable('web_cache')){

      // Retrieve all cache key from WebCache db
      WebCache::get()->each(function($model) use(&$cleared_keys){
        $cleared_keys[] = $model->key;
      });

      // Remove all entries from WebCache db
      WebCache::truncate();

    }

    // Remove all caches
    Artisan::call('cache:clear');

    $count = count($cleared_keys); // Get count of cache cleared for returning purpose

    // Create jobs to reload cache url
    file_put_contents(storage_path('logs/web-cache.log'), "[CLEAR ALL @" . Carbon::now()->toDateTimeString() . "]" . PHP_EOL);
    do{
      $keys = array_splice($cleared_keys, 0, 3);
      WebCacheLoad::dispatch($keys);
    }
    while(count($cleared_keys) > 0);

    return Schema::hasTable('web_cache') ? $count : -1;

  }

  public function clear($tag){

    $cleared_keys = [];

    if(Schema::hasTable('web_cache')){

      // Search caches by tag, immediately remove the cache and WebCache db
      WebCache::search($tag)->get()->each(function($model) use(&$cleared_keys){

        $cleared_keys[] = $model->key;

        Cache::forget($model->key);

        $model->delete();

      });

    }

    $count = count($cleared_keys); // Get count of cache cleared for returning purpose

    // Create jobs to reload cache url
    file_put_contents(storage_path('logs/web-cache.log'), "[CLEAR '{$tag}' @" . Carbon::now()->toDateTimeString() . "]" . PHP_EOL);
    do{
      $keys = array_splice($cleared_keys, 0, 3);
      WebCacheLoad::dispatch($keys);
    }
    while(count($cleared_keys) > 0);

    return Schema::hasTable('web_cache') ? $count : -1;

  }

}