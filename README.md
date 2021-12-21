
## News App Laravel

This code repo is built by Laravel framework.

## Brief

The app we are building today is a proof of concept prototype towards a
news search / research tool where a user could:

- Search against one or more news sites / services for terms they are
  interested in
- Get a collated list of results, click through to read further the
  articles of interest.


## Spec
A web endpoint that takes a user search query as a string
* It should use this string to search against at least two different news sources
* One of those can be the guardian as you used already
* The New York Times has a similar API you could use:
https://developer.nytimes.com/docs/articlesearch-product/1/overview



## Setup

Grab the repo and install the dependencies.

```bash
git clone git@github.com:raycui2011/news-app-v1.git
cd news-app-v1
composer install
```
### Environment variables

Rename `.env.example` to `.env`

## Running your app

Run the application with:

```bash
php artisan serve
```

Runs the app in the development mode.<br />
Lanuch postman, and
Set the request type to POST and the request URL to http://127.0.0.1:8000/api/news. Then set Body to form-data. see below
| key                 | value         |
| ----------------    |:-------------:|
| term                | australia     |
| page                | 1             |
|filter[0][news_desk] |Sports,Foreign |
|filter[1][glocations]|NEW YORK CITY  |


Also you can send a POST request like http://127.0.0.1:8000/api/news
