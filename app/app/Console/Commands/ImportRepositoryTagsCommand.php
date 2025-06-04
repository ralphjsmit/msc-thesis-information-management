<?php

namespace App\Console\Commands;

use App\Jobs\ImportRepositoryTags;
use App\Models\Repository;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportRepositoryTagsCommand extends Command
{
    protected $signature = 'import-repository-tags';

    public function handle(): int
    {
        DB::disableQueryLog();

        $query = Repository::query()
            ->whereNull('tags_imported_at');

        $this->info('Importing repository tags for ' . $query->count() . ' repositories');

        $this->withProgressBar($query->lazyById(), function (Repository $repository) {
            dispatch(new ImportRepositoryTags($repository));
        });

        return static::SUCCESS;
    }
}
