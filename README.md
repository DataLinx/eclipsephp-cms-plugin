# Eclipse Filament plugin template

![Packagist PHP Version Support](https://img.shields.io/packagist/php-v/eclipsephp/plugin-template)
![Packagist Version](https://img.shields.io/packagist/v/eclipsephp/plugin-template)
![Packagist Downloads](https://img.shields.io/packagist/dt/eclipsephp/plugin-template)
[![Tests](https://github.com/DataLinx/eclipsephp-plugin-template/actions/workflows/test-runner.yml/badge.svg)](https://github.com/DataLinx/eclipsephp-plugin-template/actions/workflows/test-runner.yml)
[![codecov](https://codecov.io/gh/DataLinx/eclipsephp-plugin-template/graph/badge.svg?token=1HKSY5O6IW)](https://codecov.io/gh/DataLinx/eclipsephp-plugin-template)
[![Conventional Commits](https://img.shields.io/badge/Conventional%20Commits-1.0.0-%23FE5196?logo=conventionalcommits&logoColor=white)](https://conventionalcommits.org)
![Packagist License](https://img.shields.io/packagist/l/eclipsephp/plugin-template)

## About
This package serves as a Filament plugin template for plugins developed by DataLinx for [Eclipse](https://github.com/DataLinx/eclipsephp-app), our web app based on Filament. It is also a reference of how such a package should be configured.

The template is opinionated — it's based on our tech stack, which includes JetBrains PhpStorm.

This template works on and expands our [PHP package template](https://github.com/DataLinx/php-package-template).

## Requirements
- PHP >= 8.2 (due to Pest 3 requirement)
- Filament 3
- Filament Shield plugin (to manage permissions)

See [composer.json](composer.json) for details.

## Getting started
* Download it with composer:
```shell
  composer require eclipsephp/plugin-template
````
* _Provide a simple example how to use the package._

## Contributing

### Issues
If you have some suggestions how to make this package better, please open an issue or even better, submit a pull request.

Should you want to contribute, please see the development guidelines in the [DataLinx PHP package template](https://github.com/DataLinx/php-package-template).

### Development

#### Requirements
* Linux, Mac or Windows with WSL
* [Lando](https://lando.dev/) (optional, but easier to start with)

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
4. Happy coding 😉

### Changelog
All notable changes to this project are automatically documented in the [CHANGELOG.md](CHANGELOG.md) file using the release workflow, based on the [release-please](https://github.com/googleapis/release-please) GitHub action.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

For all this to work, commit messages must follow the [Conventional commits](https://www.conventionalcommits.org/) specification, which is also enforced by a Git hook.
