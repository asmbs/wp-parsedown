# WP Parsedown

> Parsedown wrapper for WordPress with a live preview editor

This plugin serves two purposes:

- Replaces the classic WP editor with a Markdown editor with live preview ([SimpleMDE](https://github.com/sparksuite/simplemde-markdown-editor))
- Forces all content to be rendered through [Parsedown](https://github.com/erusev/parsedown), converting saved Markdown content to HTML

This is a fork and a departure from the original [friartuck6000/wp-parsedown](https://github.com/friartuck6000/wp-parsedown).

The goal is to allow your site editors to use simplified, clean, familiar formatting.

An `[image]` shortcode is also included for optional use, as Markdown only allows for crude image insertion. See the `docs` folder for more information.



## Requirements

- PHP 7.2+
- Composer
- "Classic Editor" plugin (if using WP v5+)



## Installation

1. Install with Composer:

   ```
   composer require asmbs/wp-parsedown
   ```

2. Activate the plugin.



## Development

#### Requirements

- NPM

#### Getting Started

To install the development dependencies, run:

```
composer install
npm install
```

To rebuild the assets, run:

```
npx webpack
```

(Requires [npx](https://www.npmjs.com/package/npx))