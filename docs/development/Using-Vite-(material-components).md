Take a look at the official [documentation](https://material.io/develop/web) by material. However there are some additional steps which are necessary in our project.

1. Installation

```
yarn add @material/PACKAGE_NAME
```

2. Rebuild the docker Container

3. Styles

Add CSS imports only where a stylesheet needs a shared dependency; native CSS nesting is supported directly.

4. JavaScript instantiation

Create a JS file in assets/js/ and instantiate the components.

5. Add entry of this file to `vite.config.mjs`

```
const jsEntries = {
  // ...
  ENTRY_NAME: './assets/js/FILE.js',
}
```

6. Edit the html.twig file
   Add the html to your html.twig file.
   Insert into the js block at the end of the file:

```
{{ vite_entry_script_tags('ENTRY_NAME') }}
{{ vite_entry_link_tags('ENTRY_NAME') }}
```

7. Run the asset build:

```
docker exec -it app.catroweb yarn dev
```

Or for live HMR while iterating locally:

```
yarn run dev-server
```
