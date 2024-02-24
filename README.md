# plugin-performance-analysis
A plugin that analyzes historical performance across various metrics for Wordpress plugins. As an activated plugin version changes, a new analysis snapshot is created to compare against.
# {{Plugin Starter}}

This repository contains the {{Plugin Starter}} MU plugin, powered by [the StellarWP Plugin Framework](https://github.com/stellarwp/plugin-framework).

## Installation

As this is an MU plugin, it should be cloned into the `wp-content/mu-plugins/` directory of a WordPress site:

```sh
$ git clone git@github.com:stellarwp/plugin-starter.git /path/to/wp-content/mu-plugins/plugin-starter
```

This plugin's dependencies are managed by [Composer](https://getcomposer.org) and [npm](https://nodejs.org); after cloning the repository, `cd` into the project directory and run the following:

```sh
$ composer install && npm install
```

By default, WordPress only looks for single PHP files in the `wp-content/mu-plugins/` directory to load. For production environments this will be `plugin-starter.php`, but in development we need to create our own bootstrap file, `wp-content/mu-plugins/stellarwp-bootstrap.php`, that lets us set environment variables and then load the actual bootstrap file:

```sh
$ composer make:bootstrap
```

## Building the plugin

Assets like JavaScript and CSS are handled by [Laravel Mix](https://laravel-mix.com), which is a wrapper around Webkit that exposes a nice interface for defining how files should be handled.

The **source** files for these assets should live in the `resources` directory (e.g. `resources/js/scripts.js`); when Laravel Mix is run, it will then compile these assets into the `plugin-starter/assets/` directory.

The most common usage of Laravel Mix will be building development scripts, which can be done with either of the following:

```sh
# Via npx
$ npx mix

# Via npm script
$ npm run development
```

You may also tell Mix to watch for changes with either of the following:

```sh
# Via npx
$ npx mix watch

# Via npm script
$ npm run watch
```

### Building for production

To build a production-ready version of the plugin, you may run the following command:

```sh
$ composer build
```

This will generate a `plugin-starter.zip` archive, which can be attached to a GitHub release. Development dependencies will not be installed, and all JavaScript and CSS will be optimized for production.

## Contributing

Documentation on the functionality that comes from the StellarWP Plugin Framework [can be found in that repository](https://github.com/stellarwp/plugin-framework).

For information on branching strategies, coding standards, and more [please see the Contributor Guidelines](.github/CONTRIBUTING.md).
