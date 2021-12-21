<?php

namespace App\Repositories;

use App\Interfaces\CacheRepositoryInterface;
use App\Models\Cache;

class CacheRepository implements CacheRepositoryInterface
{
    public function getAllCache()
    {
        return Cache::all();
    }

    public function getCacheByKey($key)
    {
        return Cache::findOrFail($key);
    }

    public function CreateCache(array $cache)
    {
        return Cache::create($cache);
    }
}
