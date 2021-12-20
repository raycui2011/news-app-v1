<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Requests\SendNewsApiRequest;
use Illuminate\Support\Facades\Config;
use App\Http\Controllers\Controller;
use App\Services\NewsService;
use Illuminate\Support\Facades\App;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Cache;


class ApiController extends Controller
{

    protected $url;
    protected $newsServices;
    //nytimes, guardianapi
    protected $newsSources;
    protected $timeInMinutes = 5 * 60;


    // data is not found using our $cache_key
    // it will return null value for given $cache_key

    public function __construct(NewsService $news)
    {
        $this->newsServices = $news;
        $this->newsSources = [ 'nytimes' => ['api_key' => Config::get('services.nytimes.key'), 'url' => Config::get('services.nytimes.url')],
          'guardianapi' => ['api_key' => Config::get('services.guardianapi.key'), 'url' => Config::get('services.guardianapi.url')]
        ];
    }

    /**
    * Display a listing of the news.
    * @param App\Http\Requests\SendNewsApiRequest
    * @return \Illuminate\Http\Response
    */
    public function list(SendNewsApiRequest $request)
    {
        try {
              // first try to get the data from the cache
                $searchTerm = $request->input('term');
                $urlData = $this->retriveUrl($request);
                $cacheKey = $this->getCacheKey($searchTerm);
                //read data from cache
                if (Cache::has($cacheKey)) {
                  $data = Cache::get($cacheKey);
                } else {
                  foreach ($this->newsSources as $newsSource => $sourceData) {
                      $this->newsServices->setApiUrl($urlData[$newsSource]['url']);
                      $data[$newsSource] = $this->newsServices->getNews();
                  }
                  // save the respoonse to cache
                  Cache::put($cacheKey, $data, $this->timeInMinutes);
                }
        } catch (Exception $exception) {
            if ($exception instanceof ModelNotFoundException) {
                return response()->json(['error' => 'Entry for '.str_replace('App\\', '', $exception->getModel()).' not found'], 404);
            } else if ($exception instanceof RequestException) {
                return response()->json(['error' => 'External API call failed.'], 500);
            }
        }

        return response()->json(['success' => true, 'data' => $data], 200);
    }

    /**
    * gerenate the cache key based on the search term
    *  todo could be better using all the post data
    * @param string
    * @return string
    **/
    private function getCacheKey($term) {
        return md5($term);
    }

    /**
    * This function is used to generate api url for new yorks times and guardianapi
    * @param App\Http\Requests\SendNewsApiRequest
    * @return array
    */

    private function retriveUrl(SendNewsApiRequest $request)
    {
      $searchTerm = $request->input('term');
      $page = 1;
      if (!empty($request->input('page'))) {
        $page = $request->input('page');
      }

      // for new your times
      $filters = $request->input('filter');
      $filterStr = $this->getFilterForNYTimes($filters);
      $nytimesUrl = $this->newsSources['nytimes']['url'] . '?q=' . $searchTerm . $filterStr . '&page=' . $page . '&api-key=' . $this->newsSources['nytimes']['api_key'];
      $guardianUrl = $this->newsSources['guardianapi']['url'] . '?q=' . $searchTerm . '&p=' . $page . '&api-key=' . $this->newsSources['guardianapi']['api_key'];

      return ['nytimes' => ['url' => $nytimesUrl], 'guardianapi' => ['url' => $guardianUrl]];
    }

    /**
    * This function is used to generate filtere string for new yorks times
    * for example fq=news_desk:("Sports") AND glocations:("NEW YORK CITY")
    * @param array
    * @return string $filterStr
    */
    private function getFilterForNYTimes($filters) {
      $filterStr = '';
      $and = 'and ';
      if (count($filters) > 0) {
        $count = count($filters);
        foreach ($filters as $index => $fieldArr) {
          if ($index == $count-1) {
            $and = ' ';
          }
          if (is_array($fieldArr)) {
            foreach ($fieldArr as $field => $value) {
              $filterStr.= $field . ": (\"" . $value . "\") " . $and;
            }
          }
        }
      }
      if (!empty($filterStr)) {
        $filterStr = '&fq=' . $filterStr;
      }
      return $filterStr;
    }
}
