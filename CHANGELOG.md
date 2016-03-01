# Changelog

## 1.0.0
* Added composer support and majorly refined codebase
* Now uses the [Ace](https://ace.c9.io/#nav=about) editor for more Markdown-friendly editing
* **Removed** the live preview meta box; it'll be back soon!

-----

### 0.5.3

* Switched to using a character limit (16k characters) instead of time limit

### 0.5.2

* Added a timeout to the previewer AJAX call to prevent hanging when trying to parse an enormous page.

### 0.5.1

* Updated Parsedown to `v1.0.1`
* Added [ParsedownExtra][parsedown-extra], `v0.2.2`
* Altered Parsedown core to disable [GitHub Flavored Markdown][gfm]'s autolinking feature (it was breaking shortcode attributes)