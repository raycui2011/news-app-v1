<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;

class NewsService
{
    protected $url;

    public function setApiUrl($url) {
        $this->url = $url;
    }
    /**
      *get $url
    */
    public function getApiUrl() {

    }
    /**
      *Send api call to get the news
      *
    */
    public function getNews()
    {
       $response = Http::acceptJson()->get($this->url);

       return json_decode($response->getBody());
    }
}
