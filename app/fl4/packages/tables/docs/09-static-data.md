---
title: Static data
---
import Aside from "@components/Aside.astro"

## Introduction

[Filament's table builder](overview/#introduction) was originally designed to render data directly from a SQL database using [Eloquent models](https://laravel.com/docs/eloquent) in a Laravel application. Each row in a Filament table corresponds to a row in the database, represented by an Eloquent model instance.

However, this setup isn't always possible or practical. You might need to display data that isn't stored in a database—or data that is stored, but not accessible via Eloquent.

In such cases, you can use static data instead. Pass a function to the `records()` method of the table builder that returns an array of data. This function is called when the table renders, and the value it returns is used to populate the table.

```php
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

public function table(Table $table): Table
{
    return $table
        ->records(fn (): array => [
            1 => [
                'title' => 'First item',
                'slug' => 'first-item',
                'is_featured' => true,
            ],
            2 => [
                'title' => 'Second item',
                'slug' => 'second-item',
                'is_featured' => false,
            ],
            3 => [
                'title' => 'Third item',
                'slug' => 'third-item',
                'is_featured' => true,
            ],
        ])
        ->columns([
            TextColumn::make('title'),
            TextColumn::make('slug'),
            IconColumn::make('is_featured')
                ->boolean(),
        ]);
}
```

<Aside variant="warning">
    The array keys `(e.g., 1, 2, 3)` represent the record IDs. Use unique and consistent keys to ensure proper diffing and state tracking. This helps prevent issues with record integrity during Livewire interactions and updates.
</Aside>

## Columns

[Columns](columns) in the table work similarly to how they do when using [Eloquent models](https://laravel.com/docs/eloquent), but with one key difference: instead of referring to a model attribute or relationship, the column name represents a key in the array returned by the `records()` function.

When working with the current record inside a column function, set the `$record` type to `array` instead of `Model`. For example, to define a column using the [`state()`](columns#setting-the-state-of-a-column) function, you could do the following:

```php
use Filament\Tables\Columns\TextColumn;

TextColumn::make('is_featured')
    ->state(function (array $record): string {
        return $record['is_featured'] ? 'Featured' : 'Not featured';
    })
```

### Sorting

Filament's built-in [sorting](columns#sorting) function uses SQL to sort data. When working with static data, you'll need to handle sorting yourself.

To access the currently sorted column and direction, you can inject `$sortColumn` and `$sortDirection` into the `records()` function. These variables are `null` if no sorting is applied.

In the example below, a [collection](https://laravel.com/docs/collections#method-sortby) is used to sort the data by key. The collection is returned instead of an array, and Filament handles it the same way. However, using a collection is not required to use this feature.

```php
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Collection;

public function table(Table $table): Table
{
    return $table
        ->records(
            fn (?string $sortColumn, ?string $sortDirection): Collection => collect([
                1 => ['title' => 'First item'],
                2 => ['title' => 'Second item'],
                3 => ['title' => 'Third item'],
            ])->when(
                filled($sortColumn),
                fn (Collection $data): Collection => $data->sortBy(
                    $sortColumn,
                    SORT_REGULAR,
                    $sortDirection === 'desc',
                ),
            )
        )
        ->columns([
            TextColumn::make('title')
                ->sortable(),
        ]);
}
```

<Aside variant="info">
    It might seem like Filament should sort the data for you, but in many cases, it's better to let your data source—like a custom query or API call—handle the sorting instead.
</Aside>

### Searching

Filament's built-in [searching](columns#searching) function uses SQL to search data. When working with static data, you'll need to handle searching yourself.

To access the current search query, you can inject `$search` into the `records()` function. This variable is `null` if no search query is currently being used.

In the example below, a [collection](https://laravel.com/docs/collections#method-filter) is used to filter the data by the search query. The collection is returned instead of an array, and Filament handles it the same way. However, using a collection is not required to use this feature.

```php
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

public function table(Table $table): Table
{
    return $table
        ->records(
            fn (?string $search): Collection => collect([
                1 => ['title' => 'First item'],
                2 => ['title' => 'Second item'],
                3 => ['title' => 'Third item'],
            ])->when(
                filled($search),
                fn (Collection $data): Collection => $data->filter(
                    fn (array $record): bool => str_contains(
                        Str::lower($record['title']),
                        Str::lower($search),
                    ),
                ),
            )
        )
        ->columns([
            TextColumn::make('title'),
        ])
        ->searchable();
}
```

In this example, specific columns like `title` do not need to be `searchable()` because the search logic is handled inside the `records()` function. However, if you want to enable the search field without enabling search for a specific column, you can use the `searchable()` method on the entire table.

<Aside variant="info">
    It might seem like Filament should search the data for you, but in many cases, it's better to let your data source—like a custom query or API call—handle the searching instead.
</Aside>

#### Searching individual columns

The [individual column searches](#searching-individually) feature provides a way to render a search field separately for each column, allowing more precise filtering. When using static data, you need to implement this feature yourself.

Instead of injecting `$search` into the `records()` function, you can inject an array of `$columnSearches`, which contains the search queries for each column.

```php
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

public function table(Table $table): Table
{
    return $table
        ->records(
            fn (array $columnSearches): Collection => collect([
                1 => ['title' => 'First item'],
                2 => ['title' => 'Second item'],
                3 => ['title' => 'Third item'],
            ])->when(
                filled($columnSearches['title'] ?? null),
                fn (Collection $data) => $data->filter(
                    fn (array $record): bool => str_contains(
                        Str::lower($record['title']),
                        Str::lower($columnSearches['title'])
                    ),
                ),
            )
        )
        ->columns([
            TextColumn::make('title')
                ->searchable(isIndividual: true),
        ]);
}
```

<Aside variant="info">
    It might seem like Filament should search the data for you, but in many cases, it's better to let your data source—like a custom query or API call—handle the searching instead.
</Aside>

## Filters

Filament also provides a way to filter data using [filters](filters). When working with static data, you'll need to handle filtering yourself. 

Filament gives you access to an array of filter data by injecting `$filters` into the `records()` function. The array contains the names of the filters as keys and the values of the filter forms themselves.

In the example below, a [collection](https://laravel.com/docs/collections#method-where) is used to filter the data. The collection is returned instead of an array, and Filament handles it the same way. However, using a collection is not required to use this feature.

```php
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Collection;

public function table(Table $table): Table
{
    return $table
        ->records(fn (array $filters): Collection => collect([
            1 => [
                'title' => 'What is Filament?',
                'slug' => 'what-is-filament',
                'author' => 'Dan Harrin',
                'is_featured' => true,
                'creation_date' => '2021-01-01',
            ],
            2 => [
                'title' => 'Top 5 best features of Filament',
                'slug' => 'top-5-features',
                'author' => 'Ryan Chandler',
                'is_featured' => false,
                'creation_date' => '2021-03-01',
            ],
            3 => [
                'title' => 'Tips for building a great Filament plugin',
                'slug' => 'plugin-tips',
                'author' => 'Zep Fietje',
                'is_featured' => true,
                'creation_date' => '2023-06-01',
            ],
        ])
            ->when(
                $filters['is_featured']['isActive'] ?? false,
                fn (Collection $data): Collection => $data->where(
                    'is_featured', true
                ),
            )
            ->when(
                filled($author = $filters['author']['value'] ?? null),
                fn (Collection $data): Collection => $data->where(
                    'author', $author
                ),
            )
            ->when(
                filled($date = $filters['creation_date']['date'] ?? null),
                fn (Collection $data): Collection => $data->where(
                    'creation_date', $date
                ),
            )
        )
        ->columns([
            TextColumn::make('title'),
            TextColumn::make('slug'),
            IconColumn::make('is_featured')
                ->boolean(),
            TextColumn::make('author'),
        ])
        ->filters([
            Filter::make('is_featured'),
            SelectFilter::make('author')
                ->options([
                    'Dan Harrin' => 'Dan Harrin',
                    'Ryan Chandler' => 'Ryan Chandler',
                    'Zep Fietje' => 'Zep Fietje',
                ]),
            Filter::make('creation_date')
                ->schema([
                    DatePicker::make('date'),
                ]),
        ]);
}
```

Filter values aren't directly accessible via `$filters['filterName']`. Instead, each filter contains one or more form fields, and those field names are used as keys within the filter's data array. For example:

- [Checkbox](filters/overview#introduction) or [Toggle filters](filters/overview#using-a-toggle-button-instead-of-a-checkbox) without a custom schema (e.g., featured) use `isActive` as the `key`: `$filters['featured']['isActive']`

- [Select filters](filters/select#introduction) (e.g., author) use `value`: `$filters['author']['value']`

- [Custom schema filters](filters/custom#custom-filter-schemas) (e.g., creation_date) use the actual form field names. If the field is named `date`, access it like this: `$filters['creation_date']['date']`

<Aside variant="info">
    It might seem like Filament should filter the data for you, but in many cases, it's better to let your data source—like a custom query or API call—handle the filtering instead.
</Aside>

## Pagination

Filament's built-in [pagination](overview#pagination) feature uses SQL to paginate the data. When working with static data, you'll need to handle pagination yourself. 

The `$page` and `$recordsPerPage` arguments are injected into the `records()` function, and you can use them to paginate the data. A `LengthAwarePaginator` should be returned from the `records()` function, and Filament will handle the pagination links and other pagination features for you:

```php
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

public function table(Table $table): Table
{
    return $table
        ->records(function (int $page, int $recordsPerPage): LengthAwarePaginator {
            $records = collect([
                1 => ['title' => 'What is Filament?'],
                2 => ['title' => 'Top 5 best features of Filament'],
                3 => ['title' => 'Tips for building a great Filament plugin'],
            ])->forPage($page, $recordsPerPage);

            return new LengthAwarePaginator(
                $records,
                total: 30, // Total number of records across all pages
                perPage: $recordsPerPage,
                currentPage: $page,
            );
        })
        ->columns([
            TextColumn::make('title'),
        ]);
}
```

In this example, the `forPage()` method is used to paginate the data. This probably isn't the most efficient way to paginate data from a query or API, but it is a simple way to demonstrate how to paginate data from a static array.

<Aside variant="info">
    It might seem like Filament should paginate the data for you, but in many cases, it's better to let your data source—like a custom query or API call—handle the pagination instead.
</Aside>

## Actions

[Actions](actions) in the table work similarly to how they do when using [Eloquent models](https://laravel.com/docs/eloquent). The only difference is that the `$record` parameter in the action's callback function will be an `array` instead of a `Model`.

```php
use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Collection;

public function table(Table $table): Table
{
    return $table
        ->records(fn (): Collection => collect([
            1 => [
                'title' => 'What is Filament?',
                'slug' => 'what-is-filament',
            ],
            2 => [
                'title' => 'Top 5 best features of Filament',
                'slug' => 'top-5-features',
            ],
            3 => [
                'title' => 'Tips for building a great Filament plugin',
                'slug' => 'plugin-tips',
            ],
        ]))
        ->columns([
            TextColumn::make('title'),
            TextColumn::make('slug'),
        ])
        ->actions([
            Action::make('view')
                ->color('gray')
                ->icon(Heroicon::Eye)
                ->url(fn (array $record): string => route('posts.view', $record['slug'])),
        ]);
}
```

### Bulk actions

For actions that interact with a single record, the record is always present on the current table page, so the `records()` method can be used to fetch the data. However for bulk actions, records can be selected across pagination pages. If you would like to use a bulk action that selects records across pages, you need to give Filament a way to fetch records across pages, otherwise it will only return the records from the current page. The `resolveSelectedRecordsUsing()` method should accept a function which has a `$keys` parameter, and returns an array of record data:

```php
use Filament\Actions\BulkAction;
use Filament\Tables\Table;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

public function table(Table $table): Table
{
    return $table
        ->records(function (): array {
            // ...
        })
        ->resolveSelectedRecordsUsing(function (array $keys): array {
            return Arr::only([
                1 => [
                    'title' => 'First item',
                    'slug' => 'first-item',
                    'is_featured' => true,
                ],
                2 => [
                    'title' => 'Second item',
                    'slug' => 'second-item',
                    'is_featured' => false,
                ],
                3 => [
                    'title' => 'Third item',
                    'slug' => 'third-item',
                    'is_featured' => true,
                ],
            ], $keys);
        })
        ->columns([
            // ...
        ])
        ->actions([
            BulkAction::make('feature')
                ->requiresConfirmation()
                ->action(function (Collection $records): void {
                    // Do something with the collection of `$records` data
                }),
        ]);
}
```
