---
title: Image column
---
import AutoScreenshot from "@components/AutoScreenshot.astro"
import Aside from "@components/Aside.astro"
import UtilityInjection from "@components/UtilityInjection.astro"

## Introduction

Tables can render images, based on the path in the state of the column:

```php
use Filament\Tables\Columns\ImageColumn;

ImageColumn::make('avatar')
```

In this case, the `header_image` state could contain `posts/header-images/4281246003439.jpg`, which is relative to the root directory of the storage disk. The storage disk is defined in the [configuration file](../../introduction/installation#publishing-configuration), `public` by default. You can also set the `FILESYSTEM_DISK` environment variable to change this.

Alternatively, the state could contain an absolute URL to an image, such as `https://example.com/images/header.jpg`.

<AutoScreenshot name="tables/columns/image/simple" alt="Image column" version="4.x" />

## Managing the image disk

The default storage disk is defined in the [configuration file](../../introduction/installation#publishing-configuration), `public` by default. You can also set the `FILESYSTEM_DISK` environment variable to change this. If you want to deviate from the default disk, you may pass a custom disk name to the `disk()` method:

```php
use Filament\Tables\Columns\ImageColumn;

ImageColumn::make('header_image')
    ->disk('s3')
```

<UtilityInjection set="tableColumns" version="4.x">As well as allowing a static value, the `disk()` method also accepts a function to dynamically calculate it. You can inject various utilities into the function as parameters.</UtilityInjection>

## Private images

Filament can generate temporary URLs to render private images, you may set the `visibility()` to `private`:

```php
use Filament\Tables\Columns\ImageColumn;

ImageColumn::make('header_image')
    ->visibility('private')
```

<UtilityInjection set="tableColumns" version="4.x">As well as allowing a static value, the `visibility()` method also accepts a function to dynamically calculate it. You can inject various utilities into the function as parameters.</UtilityInjection>

## Customizing the size

You may customize the image size by passing a `imageWidth()` and `imageHeight()`, or both with `imageSize()`:

```php
use Filament\Tables\Columns\ImageColumn;

ImageColumn::make('header_image')
    ->imageWidth(200)

ImageColumn::make('header_image')
    ->imageHeight(50)

ImageColumn::make('avatar')
    ->imageSize(40)
```

<UtilityInjection set="tableColumns" version="4.x">As well as allowing a static values, the `imageWidth()`, `imageHeight()` and `imageSize()` methods also accept functions to dynamically calculate them. You can inject various utilities into the function as parameters.</UtilityInjection>

### Square images

You may display the image using a 1:1 aspect ratio:

```php
use Filament\Tables\Columns\ImageColumn;

ImageColumn::make('avatar')
    ->imageHeight(40)
    ->square()
```

<AutoScreenshot name="tables/columns/image/square" alt="Square image column" version="4.x" />

Optionally, you may pass a boolean value to control if the image should be square or not:

```php
use Filament\Tables\Columns\ImageColumn;

ImageColumn::make('avatar')
    ->imageHeight(40)
    ->square(FeatureFlag::active())
```

<UtilityInjection set="tableColumns" version="4.x">As well as allowing a static value, the `square()` method also accepts a function to dynamically calculate it. You can inject various utilities into the function as parameters.</UtilityInjection>

## Circular images

You may make the image fully rounded, which is useful for rendering avatars:

```php
use Filament\Tables\Columns\ImageColumn;

ImageColumn::make('avatar')
    ->imageHeight(40)
    ->circular()
```

<AutoScreenshot name="tables/columns/image/circular" alt="Circular image column" version="4.x" />

Optionally, you may pass a boolean value to control if the image should be circular or not:

```php
use Filament\Tables\Columns\ImageColumn;

ImageColumn::make('avatar')
    ->imageHeight(40)
    ->circular(FeatureFlag::active())
```

<UtilityInjection set="tableColumns" version="4.x">As well as allowing a static value, the `circular()` method also accepts a function to dynamically calculate it. You can inject various utilities into the function as parameters.</UtilityInjection>

## Adding a default image URL

You can display a placeholder image if one doesn't exist yet, by passing a URL to the `defaultImageUrl()` method:

```php
use Filament\Tables\Columns\ImageColumn;

ImageColumn::make('header_image')
    ->defaultImageUrl(url('storage/posts/header-images/default.jpg'))
```

<UtilityInjection set="tableColumns" version="4.x">As well as allowing a static value, the `defaultImageUrl()` method also accepts a function to dynamically calculate it. You can inject various utilities into the function as parameters.</UtilityInjection>

## Stacking images

You may display multiple images as a stack of overlapping images by using `stacked()`:

```php
use Filament\Tables\Columns\ImageColumn;

ImageColumn::make('colleagues.avatar')
    ->imageHeight(40)
    ->circular()
    ->stacked()
```

<AutoScreenshot name="tables/columns/image/stacked" alt="Stacked image column" version="4.x" />

Optionally, you may pass a boolean value to control if the images should be stacked or not:

```php
use Filament\Tables\Columns\ImageColumn;

ImageColumn::make('colleagues.avatar')
    ->imageHeight(40)
    ->circular()
    ->stacked(FeatureFlag::active())
```

<UtilityInjection set="tableColumns" version="4.x">As well as allowing a static value, the `stacked()` method also accepts a function to dynamically calculate it. You can inject various utilities into the function as parameters.</UtilityInjection>

### Customizing the stacked ring width

The default ring width is `3`, but you may customize it to be from `0` to `8`:

```php
use Filament\Tables\Columns\ImageColumn;

ImageColumn::make('colleagues.avatar')
    ->imageHeight(40)
    ->circular()
    ->stacked()
    ->ring(5)
```

<UtilityInjection set="tableColumns" version="4.x">As well as allowing a static value, the `ring()` method also accepts a function to dynamically calculate it. You can inject various utilities into the function as parameters.</UtilityInjection>

### Customizing the stacked overlap

The default overlap is `4`, but you may customize it to be from `0` to `8`:

```php
use Filament\Tables\Columns\ImageColumn;

ImageColumn::make('colleagues.avatar')
    ->imageHeight(40)
    ->circular()
    ->stacked()
    ->overlap(2)
```

<UtilityInjection set="tableColumns" version="4.x">As well as allowing a static value, the `overlap()` method also accepts a function to dynamically calculate it. You can inject various utilities into the function as parameters.</UtilityInjection>

## Setting a limit

You may limit the maximum number of images you want to display by passing `limit()`:

```php
use Filament\Tables\Columns\ImageColumn;

ImageColumn::make('colleagues.avatar')
    ->imageHeight(40)
    ->circular()
    ->stacked()
    ->limit(3)
```

<UtilityInjection set="tableColumns" version="4.x">As well as allowing a static value, the `limit()` method also accepts a function to dynamically calculate it. You can inject various utilities into the function as parameters.</UtilityInjection>

<AutoScreenshot name="tables/columns/image/limited" alt="Limited image column" version="4.x" />

### Showing the remaining images count

When you set a limit you may also display the count of remaining images by passing `limitedRemainingText()`.

```php
use Filament\Tables\Columns\ImageColumn;

ImageColumn::make('colleagues.avatar')
    ->imageHeight(40)
    ->circular()
    ->stacked()
    ->limit(3)
    ->limitedRemainingText()
```

<AutoScreenshot name="tables/columns/image/limited-remaining-text" alt="Limited image column with remaining text" version="4.x" />

Optionally, you may pass a boolean value to control if the remaining text should be displayed or not:

```php
use Filament\Tables\Columns\ImageColumn;

ImageColumn::make('colleagues.avatar')
    ->imageHeight(40)
    ->circular()
    ->stacked()
    ->limit(3)
    ->limitedRemainingText(FeatureFlag::active())
```

<UtilityInjection set="tableColumns" version="4.x">As well as allowing a static value, the `limitedRemainingText()` method also accepts a function to dynamically calculate it. You can inject various utilities into the function as parameters.</UtilityInjection>

#### Customizing the limited remaining text size

By default, the size of the remaining text is `TextSize::Small`. You can customize this to be `TextSize::ExtraSmall`, `TextSize::Medium` or `TextSize::Large` using the `size` parameter:

```php
use Filament\Tables\Columns\ImageColumn;
use Filament\Support\Enums\TextSize;

ImageColumn::make('colleagues.avatar')
    ->imageHeight(40)
    ->circular()
    ->stacked()
    ->limit(3)
    ->limitedRemainingText(size: TextSize::Large)
```

<UtilityInjection set="tableColumns" version="4.x">As well as allowing a static value, the `limitedRemainingText()` method also accepts a function to dynamically calculate it. You can inject various utilities into the function as parameters.</UtilityInjection>

## Prevent file existence checks

When the schema is loaded, it will automatically detect whether the images exist to prevent errors for missing files. This is all done on the backend. When using remote storage with many images, this can be time-consuming. You can use the `checkFileExistence(false)` method to disable this feature:

```php
use Filament\Tables\Columns\ImageColumn;

ImageColumn::make('attachment')
    ->checkFileExistence(false)
```

<UtilityInjection set="tableColumns" version="4.x">As well as allowing a static value, the `checkFileExistence()` method also accepts a function to dynamically calculate it. You can inject various utilities into the function as parameters.</UtilityInjection>

## Wrapping multiple images

Images can be set to wrap if they can't fit on one line, by setting `wrap()`:

```php
use Filament\Tables\Columns\ImageColumn;

ImageColumn::make('colleagues.avatar')
    ->circular()
    ->stacked()
    ->wrap()
```

<Aside variant="tip">
    The "width" for wrapping is affected by the column label, so you may need to use a shorter or hidden label to wrap more tightly.
</Aside>

## Adding extra HTML attributes to the image

You can pass extra HTML attributes to the `<img>` element via the `extraImgAttributes()` method. The attributes should be represented by an array, where the key is the attribute name and the value is the attribute value:

```php
use Filament\Tables\Columns\ImageColumn;

ImageColumn::make('logo')
    ->extraImgAttributes([
        'alt' => 'Logo',
        'loading' => 'lazy',
    ])
```

<UtilityInjection set="tableColumns" version="4.x">As well as allowing a static value, the `extraImgAttributes()` method also accepts a function to dynamically calculate it. You can inject various utilities into the function as parameters.</UtilityInjection>

By default, calling `extraImgAttributes()` multiple times will overwrite the previous attributes. If you wish to merge the attributes instead, you can pass `merge: true` to the method.
