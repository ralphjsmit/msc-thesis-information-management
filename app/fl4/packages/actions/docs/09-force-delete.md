---
title: Force-delete action
---
import UtilityInjection from "@components/UtilityInjection.astro"

## Introduction

Filament includes an action that is able to force-delete [soft deleted](https://laravel.com/docs/eloquent#soft-deleting) Eloquent records. When the trigger button is clicked, a modal asks the user for confirmation. You may use it like so:

```php
use Filament\Actions\ForceDeleteAction;

ForceDeleteAction::make()
```

Or if you want to add it as a table bulk action, so that the user can choose which rows to force delete, use `Filament\Actions\ForceDeleteBulkAction`:

```php
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Tables\Table;

public function table(Table $table): Table
{
    return $table
        ->bulkActions([
            ForceDeleteBulkAction::make(),
        ]);
}
```

## Redirecting after force-deleting

You may set up a custom redirect when the form is submitted using the `successRedirectUrl()` method:

```php
use Filament\Actions\ForceDeleteAction;

ForceDeleteAction::make()
    ->successRedirectUrl(route('posts.list'))
```

<UtilityInjection set="actions" version="4.x">As well as `$record`, the `successRedirectUrl()` function can inject various utilities as parameters.</UtilityInjection>

## Customizing the force-delete notification

When the record is successfully force-deleted, a notification is dispatched to the user, which indicates the success of their action.

To customize the title of this notification, use the `successNotificationTitle()` method:

```php
use Filament\Actions\ForceDeleteAction;

ForceDeleteAction::make()
    ->successNotificationTitle('User force-deleted')
```

<UtilityInjection set="actions" version="4.x">As well as allowing a static value, the `successNotificationTitle()` method also accepts a function to dynamically calculate it. You can inject various utilities into the function as parameters.</UtilityInjection>

You may customize the entire notification using the `successNotification()` method:

```php
use Filament\Actions\ForceDeleteAction;
use Filament\Notifications\Notification;

ForceDeleteAction::make()
    ->successNotification(
       Notification::make()
            ->success()
            ->title('User force-deleted')
            ->body('The user has been force-deleted successfully.'),
    )
```

<UtilityInjection set="actions" version="4.x" extras="Notification;;Filament\Notifications\Notification;;$notification;;The default notification object, which could be a useful starting point for customization.">As well as allowing a static value, the `successNotification()` method also accepts a function to dynamically calculate it. You can inject various utilities into the function as parameters.</UtilityInjection>

To disable the notification altogether, use the `successNotification(null)` method:

```php
use Filament\Actions\ForceDeleteAction;

ForceDeleteAction::make()
    ->successNotification(null)
```

## Lifecycle hooks

You can use the `before()` and `after()` methods to execute code before and after a record is force-deleted:

```php
use Filament\Actions\ForceDeleteAction;

ForceDeleteAction::make()
    ->before(function () {
        // ...
    })
    ->after(function () {
        // ...
    })
```

<UtilityInjection set="actions" version="4.x">These hook functions can inject various utilities as parameters.</UtilityInjection>
