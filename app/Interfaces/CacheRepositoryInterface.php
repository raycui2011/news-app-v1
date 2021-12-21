<?php

namespace App\Interfaces;

interface cacheRepositoryInterface
{
    public function getAllCache();
    public function getCacheByKey($key);
    public function CreateCache(array $cache);
}
