---
title: Upgrade Guide
---
import Aside from "@components/Aside.astro"
import Checkbox from "@components/Checkbox.astro"
import Checkboxes from "@components/Checkboxes.astro"
import Disclosure from "@components/Disclosure.astro"

> If you see anything missing from this guide, please do not hesitate to [make a pull request](https://github.com/filamentphp/filament/edit/4.x/docs/14-upgrade-guide.md) to our repository! Any help is appreciated!

## New requirements

- PHP 8.2+
- Laravel v11.15+
- Tailwind CSS v4.0+, if you are currently using Tailwind CSS v3.0 with Filament. This does not apply if you are just using a Filament panel without a custom theme CSS file.
- Filament no longer requires `doctrine/dbal`, but if your application still does, and you do not have it installed directly, you should add it to your `composer.json` file.

## Running the automated upgrade script

The first set to upgrade your Filament app is to run the automated upgrade script. This script will automatically upgrade your application to the latest version of Filament and make changes to your code, which handles most breaking changes:

```bash
composer require filament/upgrade:"^4.0" -W --dev

vendor/bin/filament-v4
```

Make sure to carefully follow the instructions, and review the changes made by the script. You may need to make some manual changes to your code afterwards, but the script should handle most of the repetitive work for you.

You can now `composer remove filament/upgrade` as you don't need it anymore.

> Some plugins you're using may not be available in v4 just yet. You could temporarily remove them from your `composer.json` file until they've been upgraded, replace them with a similar plugins that are v4-compatible, wait for the plugins to be upgraded before upgrading your app, or even write PRs to help the authors upgrade them.

### Cleaning up your code style after upgrading to v4

The automated upgrade script uses [Rector](https://getrector.org) to make changes to your code. Sometimes, the tool may change how your code is formatted, or introduce references to classes that are not yet imported.

Filament suggests using [Laravel Pint](https://laravel.com/docs/12.x/pint) or [PHP CS Fixer](https://cs.symfony.com) to clean up your code style after running the upgrade script.

Specifically, the [`fully_qualified_strict_types` rule](https://cs.symfony.com/doc/rules/import/fully_qualified_strict_types.html) and [`global_namespace_import` rule](https://cs.symfony.com/doc/rules/import/global_namespace_import.html) in these tools will fix any references to classes that are not yet imported, which is a common issue after running the upgrade script.

This is the Laravel Pint configuration that Filament uses for its own codebase if you would like a good starting point:

```json
{
    "preset": "laravel",
    "rules": {
        "blank_line_before_statement": true,
        "concat_space": {
            "spacing": "one"
        },
        "fully_qualified_strict_types": {
            "import_symbols": true
        },
        "global_namespace_import": true,
        "method_argument_space": true,
        "single_trait_insert_per_statement": true,
        "types_spaces": {
            "space": "single"
        }
    }
}
```

## Publishing the configuration file

Some changes in Filament v4 can be reverted using the configuration file. If you haven't published the configuration file yet, you can do so by running the following command:

```bash
php artisan vendor:publish --tag=filament-config
```

Firstly, the `default_filesystem_disk` in v4 is set to the `FILESYSTEM_DISK` variable instead of `FILAMENT_FILESYSTEM_DISK`. To preserve the v3 behaviour, make sure you use this setting:

```php
return [

    // ...

    'default_filesystem_disk' => env('FILAMENT_FILESYSTEM_DISK', 'public'),

    // ...

]
```

v4 introduces some changes to how Filament generates files. A new `file_generation` section has been added to the v4 configuration file, so that you can revert back to the v3 style if you would like to keep new code consistent with how it looked before upgrading. If your configuration file doesn't already have a `file_generation` section, you should add it yourself, or re-publish the configuration file and tweak it to your liking:

```php
use Filament\Support\Commands\FileGenerators\FileGenerationFlag;

return [

    // ...

    'file_generation' => [
        'flags' => [
            FileGenerationFlag::EMBEDDED_PANEL_RESOURCE_SCHEMAS, // Define new forms and infolists inside the resource class instead of a separate schema class.
            FileGenerationFlag::EMBEDDED_PANEL_RESOURCE_TABLES, // Define new tables inside the resource class instead of a separate table class.
            FileGenerationFlag::PANEL_CLUSTER_CLASSES_OUTSIDE_DIRECTORIES, // Create new cluster classes outside of their directories.
            FileGenerationFlag::PANEL_RESOURCE_CLASSES_OUTSIDE_DIRECTORIES, // Create new resource classes outside of their directories.
            FileGenerationFlag::PARTIAL_IMPORTS, // Partially import components such as form fields and table columns instead of importing each component explicitly.
        ],
    ],

    // ...

]
```

## Breaking changes that must be handled manually

<div x-data="{ packages: ['panels', 'forms', 'infolists', 'tables', 'actions', 'notifications', 'widgets', 'support'] }">

To begin, filter the upgrade guide for your specific needs by selecting only the packages that you use in your project:

<Checkboxes>
    <Checkbox value="panels" model="packages">
        Panels
    </Checkbox>

    <Checkbox value="forms" model="packages">
        Forms

        <span slot="description">
            This package is also often used in a panel, or using the tables or actions package.
        </span>
    </Checkbox>

    <Checkbox value="infolists" model="packages">
        Infolists

        <span slot="description">
            This package is also often used in a panel, or using the tables or actions package.
        </span>
    </Checkbox>

    <Checkbox value="tables" model="packages">
        Tables

        <span slot="description">
            This package is also often used in a panel.
        </span>
    </Checkbox>

    <Checkbox value="actions" model="packages">
        Actions

        <span slot="description">
            This package is also often used in a panel.
        </span>
    </Checkbox>

    <Checkbox value="notifications" model="packages">
        Notifications

        <span slot="description">
            This package is also often used in a panel.
        </span>
    </Checkbox>

    <Checkbox value="widgets" model="packages">
        Widgets

        <span slot="description">
            This package is also often used in a panel.
        </span>
    </Checkbox>

    <Checkbox value="support" model="packages">
        Blade UI components
    </Checkbox>
</Checkboxes>

### High-impact changes

<Disclosure open x-show="packages.includes('tables')">
<span slot="summary">Changes to table filters are deferred by default</span>

The `deferFilters()` method from Filament v3 is now the default behavior in Filament v4, so users must click a button before the filters are applied to the table. To disable this behavior, you can use the `deferFilters(false)` method.

```php
use Filament\Tables\Table;

public function table(Table $table): Table
{
    return $table
        ->deferFilters(false);
}
```

<Aside variant="tip">
    You can preserve the old default behavior across your entire app by adding the following code in the `boot()` method of a service provider like `AppServiceProvider`:

    ```php
    use Filament\Tables\Table;

    Table::configureUsing(fn (Table $table) => $table
        ->deferFilters(false));
    ```
</Aside>
</Disclosure>

<Disclosure open x-show="packages.includes('forms') || packages.includes('infolists')">
<span slot="summary">The `Grid`, `Section` and `Fieldset` layout components now do not span all columns by default</span>

In v3, the `Grid`, `Section` and `Fieldset` layout components consumed the full width of their parent grid by default. This was inconsistent with the behavior of every other component in Filament, which only consumes one column of the grid by default. The intention was to make these components easier to integrate into the default Filament resource form and infolist, which uses a two column grid out of the box.

In v4, the `Grid`, `Section` and `Fieldset` layout components now only consume one column of the grid by default. If you want them to span all columns, you can use the `columnSpanFull()` method:

```php
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;

Fieldset::make()
    ->columnSpanFull()
    
Grid::make()
    ->columnSpanFull()

Section::make()
    ->columnSpanFull()
```

<Aside variant="tip">
    You can preserve the old default behavior across your entire app by adding the following code in the `boot()` method of a service provider like `AppServiceProvider`:

    ```php
    use Filament\Forms\Components\Fieldset;
    use Filament\Forms\Components\Grid;
    use Filament\Forms\Components\Section;

    Fieldset::configureUsing(fn (Fieldset $fieldset) => $fieldset
        ->columnSpanFull());

    Grid::configureUsing(fn (Grid $grid) => $grid
        ->columnSpanFull());

    Section::configureUsing(fn (Section $section) => $section
        ->columnSpanFull());
    ```
</Aside>
</Disclosure>

<Disclosure open x-show="packages.includes('forms')">
<span slot="summary">The `unique()` validation rule behavior for ignoring Eloquent records</span>

In v3, the `unique()` method did not ignore the current form's Eloquent record when validating by default. This behavior was enabled by the `ignoreRecord: true` parameter, or by passing a custom `ignorable` record.

In v4, the `unique()` method's `ignoreRecord` parameter defaults to `true`.

If you were previously using `unqiue()` validation rule without the `ignoreRecord` or `ignorable` parameters, you should use `ignoreRecord: false` to disable the new behavior.

<Aside variant="tip">
    You can preserve the old default behavior across your entire app by adding the following code in the `boot()` method of a service provider like `AppServiceProvider`:

    ```php
    use Filament\Forms\Components\Field;

    Field::configureUsing(fn (Field $field) => $field
        ->uniqueValidationIgnoresRecordByDefault(false));
    ```
</Aside>
</Disclosure>

<Disclosure open x-show="packages.includes('tables')">
<span slot="summary">The `all` pagination page option is not available for tables by default</span>

The `all` pagination page method is now not available for tables by default. If you want to use it on a table, you can add it to the configuration:

```php
use Filament\Tables\Table;

public function table(Table $table): Table
{
    return $table
        ->paginationPageOptions([5, 10, 25, 50, 'all']);
}
```

Be aware when using `all` as it will cause performance issues when dealing with a large number of records.

<Aside variant="tip">
    You can preserve the old default behavior across your entire app by adding the following code in the `boot()` method of a service provider like `AppServiceProvider`:

    ```php
    use Filament\Tables\Table;

    Table::configureUsing(fn (Table $table) => $table
        ->paginationPageOptions([5, 10, 25, 50, 'all']));
    ```
</Aside>
</Disclosure>

### Medium-impact changes

<Disclosure>
<span slot="summary">Colors must be registered before they are used</span>

In Filament v3, when using a color, for example in an action, table column or infolist entry `color()` method, or if an enum uses the `HasColor` trait, you could use an array of RGB values instead of a registered color name. Filament v4 introduces a more advanced color system, and now all colors must be registered before they are used.

For example, v3 allowed this code:

```php
use Filament\Actions\Action;

Action::make('delete')
    ->color([
        50 => '254, 242, 242',
        100 => '254, 226, 226',
        200 => '254, 202, 202',
        300 => '252, 165, 165',
        400 => '248, 113, 113',
        500 => '239, 68, 68',
        600 => '220, 38, 38',
        700 => '185, 28, 28',
        800 => '153, 27, 27',
        900 => '127, 29, 29',
        950 => '69, 10, 10',
    ])
```

In Filament v4, you should first register the color in the panel you are using the component in:

```php
use Filament\Panel;
use Filament\Support\Colors\Color;

public function panel(Panel $panel): Panel
{
    return $panel
        // ...
        ->colors([
            'ruby' => [
                50 => '254, 242, 242',
                100 => '254, 226, 226',
                200 => '254, 202, 202',
                300 => '252, 165, 165',
                400 => '248, 113, 113',
                500 => '239, 68, 68',
                600 => '220, 38, 38',
                700 => '185, 28, 28',
                800 => '153, 27, 27',
                900 => '127, 29, 29',
                950 => '69, 10, 10',
            ],
        ]);
}
```

Or if you're not using a panel, you can register the color in the `boot()` method of a service provider like `AppServiceProvider`:

```php
use Filament\Support\Facades\FilamentColor;

FilamentColor::register([
    'ruby' => [
        50 => '254, 242, 242',
        100 => '254, 226, 226',
        200 => '254, 202, 202',
        300 => '252, 165, 165',
        400 => '248, 113, 113',
        500 => '239, 68, 68',
        600 => '220, 38, 38',
        700 => '185, 28, 28',
        800 => '153, 27, 27',
        900 => '127, 29, 29',
        950 => '69, 10, 10',
    ],
]);
```

Then you can use the color in your component:

```php
use Filament\Actions\Action;

Action::make('delete')
    ->color('ruby');
```
</Disclosure>

<Disclosure x-show="packages.includes('panels')">
<span slot="summary">Automatic tenancy global scoping and association</span>

When using tenancy in v3, Filament only scoped resource queries to the current tenant: to render the resource table, resolve URL parameters, and fetch global search results. There were many situations where other queries in the panel were not scoped by default, and the developer had to manually scope them. While this was a documented feature, it created a lot of additional work for developers.

In v4, Filament automatically scopes all queries in a panel to the current tenant, and automatically associates new records with the current tenant using model events. This means that you no longer need to manually scope queries or associate new Eloquent records in most cases. There are still some important points to consider, so the [documentation](users/tenancy#tenancy-security) has been updated to reflect this.
</Disclosure>

<Disclosure x-show="packages.includes('forms')">
<span slot="summary">The `Radio` `inline()` method behavior</span>

In v3, the `inline()` method put the radio buttons inline with each other, and also inline with the label at the same time. This is inconsistent with other components.

In v4, the `inline()` method now only puts the radio buttons inline with each other, and not with the label. If you want the radio buttons to be inline with the label, you can use the `inlineLabel()` method as well.

If you were previously using `inline()->inlineLabel(false)` to achieve the v4 behavior, you can now simply use `inline()`.

<Aside variant="tip">
    You can preserve the old default behavior across your entire app by adding the following code in the `boot()` method of a service provider like `AppServiceProvider`:

    ```php
    use Filament\Forms\Components\Radio;
    
    Radio::configureUsing(fn (Radio $radio) => $radio
        ->inlineLabel(fn (): bool => $radio->isInline()));
    ```
</Aside>
</Disclosure>

<Disclosure x-show="packages.includes('actions')">
<span slot="summary">Import and export job retries</span>

In Filament v3, import and export jobs were retries continuously for 24 hours if they failed, with no backoff between tries by default. This caused issues for some users, as there was no backoff period and the jobs could be retried too quickly, causing the queue to be flooded with continuously failing jobs.

In v4, they are retried 3 times with a 60 second backoff between each retry.

This behavior can be customized in the [importer](import#customizing-the-import-job-retries) and [exporter](export#customizing-the-export-job-retries) classes.
</Disclosure>

### Low-impact changes

<Disclosure>
<span slot="summary">The color shade customization API has been removed</span>

The API to customize color shades, `FilamentColor::addShades()`, `FilamentColor::overrideShades()`, and `FilamentColor::removeShades()` has been removed. This API was replaced with a more advanced color system in v4 which uses different shades for components based on their contrast, to ensure that components are accessible.
</Disclosure>

<Disclosure x-show="packages.includes('tables') || packages.includes('infolists')">
<span slot="summary">The `isSeparate` parameter of `ImageColumn::limitedRemainingText()` and `ImageEntry::limitedRemainingText()` has been removed</span>

Previously, users were able to display the number of limited images separately to an image stack using the `isSeparate` parameter. Now the parameter has been removed, and if a stack exists, the text will always be stacked on top and not separate. If the images are not stacked, the text will be separate.
</Disclosure>

<Disclosure x-show="packages.includes('forms')">
<span slot="summary">The `RichEditor` component's `disableGrammarly()` method has been removed</span>

The `disableGrammarly()` method has been removed from the `RichEditor` component. This method was used to disable the Grammarly browser extension acting on the editor. Since moving the underlying implementation of the editor from Trix to TipTap, we have not found a way to disable Grammarly on the editor.
</Disclosure>

<Disclosure x-show="packages.includes('forms')">
<span slot="summary">Overriding the `Field::make()`, `MorphToSelect::make()`, `Placeholder::make()`, or `Builder\Block::make()` methods</span>

The signature for the `Field::make()`, `MorphToSelect::make()`, `Placeholder::make()`, and `Builder\Block::make()` methods has changed. Any classes that extend the `Field`, `MorphToSelect`, `Placeholder`, or `Builder\Block` class and override the `make()` method must update the method signature to match the new signature. The new signature is as follows:

```php
public static function make(?string $name = null): static
```

This is due to the introduction of the `getDefaultName()` method, that can be overridden to provide a default `$name` value if one is not specified (`null`). If you were previously overriding the `make()` method in order to provide a default `$name` value, it is advised that you now override the `getDefaultName()` method instead, to avoid further maintenance burden in the future:

```php
public static function getDefaultName(): ?string
{
    return 'default';
}
```

If you are overriding the `make()` method to pass default configuration to the object once it is instantiated, please note that it is recommended to instead override the `setUp()` method, which is called immediately after the object is instantiated:

```php
protected function setUp(): void
{
    parent::setUp();

    $this->label('Default label');
}
```

Ideally, you should avoid overriding the `make()` method altogether as there are alternatives like `setUp()`, and doing so causes your code to be brittle if Filament decides to introduce new constructor parameters in the future.
</Disclosure>

<Disclosure x-show="packages.includes('infolists')">
<span slot="summary">Overriding the `Entry::make()` method</span>

The signature for the `Entry::make()` method has changed. Any classes that extend the `Entry` class and override the `make()` method must update the method signature to match the new signature. The new signature is as follows:

```php
public static function make(?string $name = null): static
```

This is due to the introduction of the `getDefaultName()` method, that can be overridden to provide a default `$name` value if one is not specified (`null`). If you were previously overriding the `make()` method in order to provide a default `$name` value, it is advised that you now override the `getDefaultName()` method instead, to avoid further maintenance burden in the future:

```php
public static function getDefaultName(): ?string
{
    return 'default';
}
```

If you are overriding the `make()` method to pass default configuration to the object once it is instantiated, please note that it is recommended to instead override the `setUp()` method, which is called immediately after the object is instantiated:

```php
protected function setUp(): void
{
    parent::setUp();

    $this->label('Default label');
}
```

Ideally, you should avoid overriding the `make()` method altogether as there are alternatives like `setUp()`, and doing so causes your code to be brittle if Filament decides to introduce new constructor parameters in the future.
</Disclosure>

<Disclosure x-show="packages.includes('tables')">
<span slot="summary">Overriding the `Column::make()` or `Constraint::make()` methods</span>

The signature for the `Column::make()` and `Constraint::make()` methods has changed. Any classes that extend the `Column` or `Constraint` class and override the `make()` method must update the method signature to match the new signature. The new signature is as follows:

```php
public static function make(?string $name = null): static
```

This is due to the introduction of the `getDefaultName()` method, that can be overridden to provide a default `$name` value if one is not specified (`null`). If you were previously overriding the `make()` method in order to provide a default `$name` value, it is advised that you now override the `getDefaultName()` method instead, to avoid further maintenance burden in the future:

```php
public static function getDefaultName(): ?string
{
    return 'default';
}
```

If you are overriding the `make()` method to pass default configuration to the object once it is instantiated, please note that it is recommended to instead override the `setUp()` method, which is called immediately after the object is instantiated:

```php
protected function setUp(): void
{
    parent::setUp();

    $this->label('Default label');
}
```

Ideally, you should avoid overriding the `make()` method altogether as there are alternatives like `setUp()`, and doing so causes your code to be brittle if Filament decides to introduce new constructor parameters in the future.
</Disclosure>

<Disclosure x-show="packages.includes('actions')">
<span slot="summary">Overriding the `ExportColumn::make()` or `ImportColumn::make()` methods</span>

The signature for the `ExportColumn::make()` and `ImportColumn::make()` methods has changed. Any classes that extend the `ExportColumn` or `ImportColumn` class and override the `make()` method must update the method signature to match the new signature. The new signature is as follows:

```php
public static function make(?string $name = null): static
```

This is due to the introduction of the `getDefaultName()` method, that can be overridden to provide a default `$name` value if one is not specified (`null`). If you were previously overriding the `make()` method in order to provide a default `$name` value, it is advised that you now override the `getDefaultName()` method instead, to avoid further maintenance burden in the future:

```php
public static function getDefaultName(): ?string
{
    return 'default';
}
```

If you are overriding the `make()` method to pass default configuration to the object once it is instantiated, please note that it is recommended to instead override the `setUp()` method, which is called immediately after the object is instantiated:

```php
protected function setUp(): void
{
    parent::setUp();

    $this->label('Default label');
}
```

Ideally, you should avoid overriding the `make()` method altogether as there are alternatives like `setUp()`, and doing so causes your code to be brittle if Filament decides to introduce new constructor parameters in the future.
</Disclosure>

<Disclosure x-show="packages.includes('actions')">
<span slot="summary">Authenticating the user inside the import and export jobs</span>

In v3, the `Illuminate\Auth\Events\Login` event was fired from the import and export jobs, to set the current user. This is no longer the case in v4: the user is authenticated, but that event is not fired, to avoid running any listeners that should only run for actual user logins.
</Disclosure>

<Disclosure x-show="packages.includes('panels')">
<span slot="summary">Overriding the `can*()` authorization methods on a `Resource`, `RelationManager` or `ManageRelatedRecords` class</span>

Although these methods, such as `canCreate()`, `canViewAny()` and `canDelete()` were not documented, if you are overriding those to provide custom authorization logic in v3, you should be aware that they are not always called in v4. The authorization logic has been improved to properly support [policy response objects](https://laravel.com/docs/authorization#policy-responses), and these methods were too simple as they are just able to return booleans.

If you can make the authorization customization inside the policy of the model instead, you should do that. If you need to customize the authorization logic in the resource or relation manager class, you should override the `get*AuthorizationResponse()` methods instead, such as `getCreateAuthorizationResponse()`, `getViewAnyAuthorizationResponse()` and `getDeleteAuthorizationResponse()`. These methods are called when the authorization logic is executed, and they return a [policy response object](https://laravel.com/docs/authorization#policy-responses). If you remove the override for the `can*()` methods, the `get*AuthorizationResponse()` methods will be used to determine the authorization response boolean, so you don't have to maintain the logic twice.
</Disclosure>

<Disclosure>
<span slot="summary">European Portuguese translations</span>

The European Portuguese translations have been moved from `pt_PT` to `pt`, which appears to be the more commonly used language code for the language within the Laravel community.
</Disclosure>

<Disclosure>
<span slot="summary">Nepalese translations</span>

The Nepalese translations have been moved from `np` to `ne`, which appears to be the more commonly used language code for the language within the Laravel community.
</Disclosure>

<Disclosure>
<span slot="summary">Norwegian translations</span>

The Norwegian translations have been moved from `no` to `nb`, which appears to be the more commonly used language code for the language within the Laravel community.
</Disclosure>

</div>
