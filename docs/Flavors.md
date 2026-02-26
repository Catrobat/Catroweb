Flavors are themed variants of the website.

Examples are `pocketcode`, `luna`, and `pocketgalaxy`.
If no flavor is specified, `pocketcode` is used by default.

### Routing
All routes in our project are prefixed with a theme.

To allow a route like share.catrob.at/newThemeName, the new theme must be added to the themes array in
_config/packages/liip_theme.yaml_ first.

### Theme Templates
By default, if a flavored URL is accessed, a theme with the same name is loaded.
In combination with twig, the actual theme can be checked
```
    {% if theme() == 'luna' %}
```
This allows us to easily add theme specific content.

### Theme Style
For every theme, there must be a settings file at
`assets/css/themes/<theme_name>/_theme_settings.scss`.
In this file, variables for colors, sizes, and spacing can be overridden.

Styles are compiled via Webpack Encore (`yarn dev`, `yarn watch`, `yarn build`), so ensure the theme styles are included in the relevant SCSS entry files.

### Admin
Flavors are stored as strings for simplicity.
Keep in mind that SonataAdmin select fields may also need to be updated separately.
