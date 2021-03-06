# Changelog

All notable changes to this project will be documented in this file.

As of v3.0.0, the format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/).

As of v3.0.0, this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).



## [Unreleased]



## [4.1.0] - 2019-09-11

### Added

- TerserWebpackPlugin to shrink JS dist files to 42% of their former size.
- CleanWebpackPlugin.

### Changed

- Switched from LESS to Sass.
- The `<figure>` classes to have a prefix to help prevent conflicts.
- Updated dependencies.

### Fixed

- The "align" attribute not actually working.



## [4.0.0] - 2019-05-06

### Added

- Styling, which is now used by default, to make images used with the shortcode more mobile-friendly.
- Responsive image tag support, which is now used by default.
- Warnings to post editing when a Markdown- or HTML-formatted image is detected.
- An "Image ID" field for convenience when viewing images in the Media Library.
- A Settings page to disable the shortcode, warnings, and the "Image ID" field.
- `width`, `responsive-opt-out`, and `max-width-opt-out` attributes to the image shortcode.

### Changed

- The `size` attribute on the image shortcode to be ignored by default. (It only takes effect when `responsive-opt-out` is used now.)
- Changed targeted browsers from >2% share to >1%.
- Updated dependencies.

### Removed

- The image button from the SimpleMDE toolbar to discourage use of Markdown-formatted images.



## [3.0.6] - 2019-01-14

### Fixed

- Fixed handling of cases where an enclosing shortcode opened and closed on the same line (e.g. `[btn]Press Here[/btn]`).



## [3.0.5] - 2018-12-21

### Fixed

- Fixed a bug in cases where an `a` tag spanned multiple lines (which is permissible per the CommonMark spec).



## [3.0.4] - 2018-12-12

### Fixed

- Fixed the toolbar disappearing (i.e. not sticking) while scrolling in long posts.



## [3.0.3] - 2018-12-07

### Fixed

- Fixed compatibility with some installations of WordPress and their provided jQuery.



## [3.0.2] - 2018-12-06

### Fixed

- Fixed compatibility with HTML that is not compliant to the CommonMark spec.



## [3.0.1] - 2018-12-04

### Fixed

- Fixed compatibility with some WordPress shortcodes that render complex HTML.



## [3.0.0] - 2018-10-19

### Changed

- Switched from Grunt to Webpack.
- Changed editor from Ace to SimpleMDE.
- Rewrote all JavaScript to modular ES6.
- Changed namespace from "Ft6k" to "ASMBS".
- Updated dependencies.

### Removed

- Dropped support for PHP <7.2.



## [2.0.1]

### Changed

- Merged in the changes from v0.5.5.



## [2.0.0]

*Same as v1.0.8 from the original project.*



## [0.5.5]

### Added

- Added Composer with the ASMBS namespace to support internal projects.

### Fixed

- Pulled in a Parsedown fix for PHP7 support.





## Archive from original project

### 1.0.8

* JS now checks to make sure the editor is enabled before trying to replace it with the Ace editor.

### 1.0.7
* Replaced `ParsedownExtra` with an extension that removes support for indent-triggered code blocks. Previously, this feature of the Markdown specification would cause shortcode-generated HTML to be interpreted as a code block.

### 1.0.6
* Fixed image sizing when using the shortcode.

### 1.0.5
* Adjusted image shortcode to use better figure classes.
* Removed ID attributes from both the `figure` and `img` so it's possible to use the same image twice on one page.
* Removed a bunch of leftover files from early releases.

### 1.0.4
* Yes, I had to create a hotfix for not incrementing the version number of my last hotfix. Leave me alone.

### 1.0.3
_Well, that didn't take long._

* Replaced the quote unescaper with a filter to add `markdown="1"	` to block-level elements, taking advantage of [Markdown Extra's ability](https://michelf.ca/projects/php-markdown/extra/#markdown-attr) to handle nested formatting natively.
* Adjusted filter priorities _again_

### 1.0.2
* Adjusted filter priorities to make sure Markdown parsing is done at the correct point in the filter chain.
* Added an additional filter to unescape quotes in shortcode definitions, since shortcodes must be processed _after_ parsing. 
* Refactored image shortcode.

### 1.0.1
* Removed a duplicate submit binding in the editor JS that was causing post previews to fail.

### 1.0.0
* Added composer support and majorly refined codebase
* Now uses the [Ace](https://ace.c9.io/#nav=about) editor for more Markdown-friendly editing
* **Removed** the live preview meta box; it'll be back soon!

### 0.5.3

* Switched to using a character limit (16k characters) instead of time limit

### 0.5.2

* Added a timeout to the previewer AJAX call to prevent hanging when trying to parse an enormous page.

### 0.5.1

* Updated Parsedown to `v1.0.1`
* Added [ParsedownExtra][parsedown-extra], `v0.2.2`
* Altered Parsedown core to disable [GitHub Flavored Markdown][gfm]'s autolinking feature (it was breaking shortcode attributes)
