<?php

namespace App\Console\Commands;

use App\Jobs\ImportRepositoryMonths;
use App\Models\Repository;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportRepositoryMonthsCommand extends Command
{
    protected $signature = 'import-repository-months';

    public function handle(): int
    {
        DB::disableQueryLog();

        $query = Repository::query()
            ->whereNull('repository_months_imported_at');

        $this->info('Importing repository months for ' . $query->count() . ' repositories');

        $this->withProgressBar($query->lazyById(), function (Repository $repository) {
            dispatch(new ImportRepositoryMonths($repository));
        });

        return static::SUCCESS;
    }
}
