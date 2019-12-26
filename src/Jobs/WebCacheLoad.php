<?php

namespace Andiwijaya\WebCache\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Ixudra\Curl\Facades\Curl;

class WebCacheLoad implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $keys;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $keys){

      $this->keys = $keys;

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(){

      $logs = [];

      foreach($this->keys as $key){

        $keys = explode('#', $key);
        $url = $keys[1];
        $device = $keys[2];
        $user_agent = $device == 'm' ? 'Mozilla/5.0 (iPhone; CPU iPhone OS 11_0 like Mac OS X) AppleWebKit/604.1.38 (KHTML, like Gecko) Version/11.0 Mobile/15A372 Safari/604.1' :
          'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/78.0.3904.108 Safari/537.36';

        Curl::to($url)
          ->withHeader("User-Agent: {$user_agent}")
          ->get();

        $logs[] = implode("\t", $keys);

      }

      file_put_contents(storage_path('logs/web-cache.log'), implode(PHP_EOL, $logs), FILE_APPEND);

    }

}
