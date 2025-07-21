# Eclipse Filament CMS plugin

![Packagist PHP Version Support](https://img.shields.io/packagist/php-v/eclipsephp/cms-plugin)
![Packagist Version](https://img.shields.io/packagist/v/eclipsephp/cms-plugin)
![Packagist Downloads](https://img.shields.io/packagist/dt/eclipsephp/cms-plugin)
[![Tests](https://github.com/DataLinx/eclipsephp-cms-plugin/actions/workflows/test-runner.yml/badge.svg)](https://github.com/DataLinx/eclipsephp-cms-plugin/actions/workflows/test-runner.yml)
[![codecov](https://codecov.io/gh/DataLinx/eclipsephp-cms-plugin/graph/badge.svg?token=1HKSY5O6IW)](https://codecov.io/gh/DataLinx/eclipsephp-cms-plugin)
[![Conventional Commits](https://img.shields.io/badge/Conventional%20Commits-1.0.0-%23FE5196?logo=conventionalcommits&logoColor=white)](https://conventionalcommits.org)
![Packagist License](https://img.shields.io/packagist/l/eclipsephp/cms-plugin)

## About
Filament plugin for product catalogue data.

* 🟧 Pages and simple sections (WIP)
* 🟧 Menus (WIP) — create hierarhical and sortable list of links  
* 🟧 Banners (WIP)  
* ⏳ Page tags (planned) — tag pages and browse them by tags  
* ⏳ Blog section (planned)  
* ⏳ News section (planned)  
* ⏳ Document section (planned) — versioned documents  
* ⏳ Locations section (planned)  
* ⏳ Photo gallery section (planned)  
* ⏳ Other resources coming when needed ([suggest a feature](https://github.com/DataLinx/eclipsephp-cms-plugin/discussions) and/or [hire us](https://www.datalinx.si))

## Requirements
- PHP >= 8.2 (due to Pest 3 requirement)
- Filament 3
- Filament Shield plugin (to manage permissions)

See [composer.json](composer.json) for details.

## Installation
1. Download the plugin with composer:
```shell
  composer require eclipsephp/cms-plugin
````
2. Add it to your panel's plugins:
```php
class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->plugins([
                \Eclipse\Cms\CmsPlugin::make(),
            ]);

        return $panel;
    }
}
```
3. Run migrations
```shell
php artisan migrate
```

## Contributing

### Issues
If you have some suggestions how to make this package better, please open an issue or even better, submit a pull request.

Should you want to contribute, please see the development guidelines in the [DataLinx PHP package template](https://github.com/DataLinx/php-package-template).

### Development

1. All development is subject to our [PHP package development guidelines](https://github.com/DataLinx/php-package-template/blob/bc39ae340e7818614ae2aaa607e97088318dd754/docs/Documentation.md).
2. Our [Filament app development docs](https://datalinx.github.io/eclipsephp-app/) will also be helpful.
3. Any PRs will generally need to adhere to these before being merged.

#### Requirements
See [here](https://datalinx.github.io/eclipsephp-app/introduction/requirements.html).

#### Get started
1. Clone the git repo
2. Start the Lando container
    ```shell
    lando start
    ````
3. Install dependencies (this also runs the setup composer script)
    ```shell
    lando composer install
    ````
4. You can now develop and run tests. Happy coding 😉

💡 To manually test the plugin in the browser, see our [recommendation](https://datalinx.github.io/eclipsephp-app/plugin-development/setting-up.html), which is also [how Filament suggests package development](https://filamentphp.com/docs/3.x/support/contributing#developing-with-a-local-copy-of-filament).  
However, the plugin should be universal and not dependent on our app setup or core package.

### Changelog
All notable changes to this project are automatically documented in the [CHANGELOG.md](CHANGELOG.md) file using the release workflow, based on the [release-please](https://github.com/googleapis/release-please) GitHub action.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

For all this to work, commit messages must follow the [Conventional commits](https://www.conventionalcommits.org/) specification, which is also enforced by a Git hook.
