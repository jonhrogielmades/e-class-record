<?php

namespace App\Database\Migrations;

use Illuminate\Database\Migrations\Migrator;
use Illuminate\Support\Collection;
use Symfony\Component\Finder\Finder;

class PathSafeMigrator extends Migrator
{
    public function getMigrationFiles($paths)
    {
        return Collection::make($paths)->flatMap(function ($path) {
            if (str_ends_with($path, '.php')) {
                return [$path];
            }

            if (! is_dir($path)) {
                return [];
            }

            return collect(Finder::create()
                ->files()
                ->in($path)
                ->depth('== 0')
                ->name('*_*.php')
                ->sortByName())
                ->map(fn ($file) => $file->getRealPath() ?: $file->getPathname())
                ->all();
        })->filter()->values()->keyBy(function ($file) {
            return $this->getMigrationName($file);
        })->sortBy(function ($file, $key) {
            return $key;
        })->all();
    }
}
