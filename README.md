[parsedown]: https://github.com/erusev/parsedown
[parsedown-extra]: https://github.com/erusev/parsedown-extra
[gfm]: https://help.github.com/articles/github-flavored-markdown

# WP Parsedown

A [Parsedown][parsedown] wrapper for WordPress. When activated, this plugin completely disables the visual editor. It also introduces a real-time preview box in the editor, so you can preview your parsed Markdown as you're writing it.

**Additional features:**

*   Inserting an image via the Media Library generates a clean shortcode, rather than an ugly `<img>` or `figure` conglomeration.
*   A cheatsheet is available under the **Help** pull-down in the editor.

## Release Notes

### v0.5.2

* Added a timeout to the previewer AJAX call to prevent hanging when trying to parse an enormous page.

### v0.5.1

* Updated Parsedown to `v1.0.1`
* Added [ParsedownExtra][parsedown-extra], `v0.2.2`
* Altered Parsedown core to disable [GitHub Flavored Markdown][gfm]'s autolinking feature (it was breaking shortcode attributes)
