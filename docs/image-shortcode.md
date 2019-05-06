# Image Shortcode

> This plugins provides an image shortcode to cover cases where image needs are not as basic as Markdown assumes, but also not so complicated to need full HTML.



## Example

```
[image id="5555" alt="Promotional image"]
```



## Attributes

**Note:** The `id` attribute is required.

| Attribute Name     | Description                                                  | Expected Value                                               | Default Value |
| ------------------ | ------------------------------------------------------------ | ------------------------------------------------------------ | ------------- |
| id                 | The ID of the image (i.e. the post ID in the Media Library). | Integer                                                      |               |
| href               | The URL to which the image should link.                      | String                                                       |               |
| alt                | Alternate text for the image. This is usually hidden except for the visually impaired, search engines, or when the image cannot be loaded. | String                                                       |               |
| caption            | Text that should always appear directly below the image.     | String                                                       |               |
| align              | How the image should be horizontally aligned on the page.    | "left",   "center", or "right"                               |               |
| width              | How wide the image should be.                                | A number in pixels (e.g. "300px"), a percentage (e.g.   "75%"), or any other value compatible with the [CSS width property](https://developer.mozilla.org/en-US/docs/Web/CSS/width) | "auto"        |
| size               | Sets the size of the image to use. This has no effect unless `responsive-opt-out` is set. | "thumbnail", "medium", "medium_large",   "large", or "full"  | "large"       |
| responsive-opt-out | Opts out of the default responsive image tag system.         | Use any value to opt out.                                    | false         |
| max-width-opt-out  | Opts out of the default mobile-friendly styling.             | Use any value to opt out.                                    | false         |



## Attribute Details

### Responsiveness

With "WP Parsedown" plugin v4.0 and after, the image tags produced are responsive by default (like [classic WP image responsiveness](https://make.wordpress.org/core/2015/11/10/responsive-images-in-wordpress-4-4/)).

If you would like to force a size, then include the `responsive-opt-out` attribute with any value and you can then use the `size` attribute.

### Mobile-Friendliness

With "WP Parsedown" plugin v4.0 and after, a style is applied to all images to make the **maximum** width of the image 100% of the user's browser view (`max-width:100%;`). 

If you would like to allow an image to overflow, then include the `max-width-opt-out` attribute with any value.