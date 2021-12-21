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

class ApiController extends Controller
{

    protected $url;
    protected $newsServices;
    //nytimes, guardianapi
    protected $newsSources;

    public function __construct(NewsService $news)
    {
        $this->newsServices = $news;
        $this->newsSources = [ 'nytimes'     => ['api_key' => Config::get('services.nytimes.key'), 'url' => Config::get('services.nytimes.url')],
          'guardianapi' => ['api_key' => Config::get('services.guardianapi.key'), 'url' => Config::get('services.guardianapi.url')]
        ];
    }

    /**
    * Display a listing of the news.
    *
    * @return \Illuminate\Http\Response
    */
    public function list(SendNewsApiRequest $request)
    {
        try {
          $urlData = $this->retriveUrl($request);
          $this->newsServices->setApiUrl($urlData['nytimes']['url']);
          $nytimesResponseObj = $this->newsServices->getNews();
          $this->newsServices->setApiUrl($urlData['guardianapi']['url']);
          $guardianResponseObj = $this->newsServices->getNews();
          //$results = ['data' => ];
        } catch (Exception $exception) {
            if ($exception instanceof ModelNotFoundException) {
                return response()->json(['error' => 'Entry for '.str_replace('App\\', '', $exception->getModel()).' not found'], 404);
            } else if ($exception instanceof RequestException) {
                return response()->json(['error' => 'External API call failed.'], 500);
            }
        }

        $data = ['nytimes' =>$nytimesResponseObj->response, 'guardian' =>$guardianResponseObj->response->results];
        return response()->json(['success' => true, 'data' => $data], 200);
    }
    /**
    * This function is used to generate api url for new yorks times and guardianapi
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
    *
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
