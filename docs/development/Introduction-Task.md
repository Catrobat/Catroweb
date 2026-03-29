# Introduction Task: "Steal a Project" Button

## Overview

Welcome to the Catroweb project! This document is a hands-on learning exercise designed to help you get familiar with the full tech stack by implementing a fun (and intentionally absurd) feature: a **"Steal Project"** button on the project page.

**This feature is purely a learning exercise and will never be deployed to production.** The goal is to walk you through every layer of the application -- from Symfony controllers and Twig templates, through JavaScript API calls, to Behat acceptance tests -- so that you have a solid mental model of how everything fits together.

By the end of this task you will have touched:

- A Symfony controller with route attributes
- A Twig template with buttons and data attributes
- JavaScript using the `fetch` API with JWT authentication
- User feedback via Snackbar and SweetAlert2
- A Behat acceptance test

## Prerequisites

Before you start, make sure you have:

1. **Docker environment running** -- follow `docs/setup/Docker.md` to get all containers up
2. **Local tooling installed** -- Node.js (with Corepack enabled for Yarn), Composer, PHP 8.5
3. **Dependencies installed** -- `composer install` and `yarn install` have been run
4. **Assets built** -- `yarn run dev` has been run at least once (or `yarn run watch` is running)
5. **Basic familiarity** with PHP, HTML, and JavaScript (Symfony experience is helpful but not required)

Verify the app is running:

```bash
curl -s -o /dev/null -w "%{http_code}" http://localhost:8080
# Should return 200
```

Reset the database with sample data so you have projects to work with:

```bash
docker exec app.catroweb bin/console catro:reset --hard --limit 20
```

## The Task

> **User Story:** As a logged-in user, when I am viewing someone else's project, I want to click a "Steal" button that transfers ownership of the project to me.

Your implementation should:

1. Show a "Steal" button on the project page (only when viewing someone else's project)
2. Ask for confirmation before stealing
3. Send an authenticated request to the backend
4. Change the project's owner in the database
5. Show success/error feedback to the user
6. Be covered by a Behat test

## Step-by-step Guide

### Step 1: Add a Route in the Controller

Controllers live in `src/Application/Controller/`. The project page is handled by `ProjectController.php` at `src/Application/Controller/Project/ProjectController.php`.

Open that file and study how existing routes are defined. Symfony uses PHP attributes for routing:

```php
#[Route(path: '/project/{id}', name: 'program', defaults: ['id' => 0])]
public function project(Request $request, string $id): Response
{
    // ...
}
```

Key things to notice:

- `#[Route(...)]` defines the URL path and a route name
- The `{id}` placeholder is passed as a method parameter
- The method returns a Symfony `Response` object

**Your task:** Add a new route for stealing a project. Think about:

- What HTTP method should it use? (Hint: it changes data, so not GET)
- What URL pattern makes sense? Something like `/project/{id}/steal`
- What route name to give it? Convention is `snake_case`, e.g., `project_steal`

Here is a skeleton to get you started:

```php
#[Route(path: '/project/{id}/steal', name: 'project_steal', methods: ['POST'])]
public function stealProject(string $id): Response
{
    // Step 1: Get the current user (return 401 if not logged in)
    // Step 2: Find the project (return 404 if not found)
    // Step 3: Check it's not already the user's project
    // Step 4: Change ownership
    // Step 5: Return a response
}
```

Look at the `markNotForKids` method in the same controller for a pattern that:

- Finds a project by ID
- Modifies it
- Persists changes with `$this->entity_manager->persist()` and `flush()`
- Returns a redirect or JSON response

**Hint:** To get the current user, use `$this->getUser()`. To change ownership, look at the `Program` entity (`src/DB/Entity/Project/Program.php`) for a `setUser()` method.

For returning JSON (which your JavaScript will expect):

```php
return new JsonResponse(['success' => true, 'message' => 'Project stolen!']);
```

### Step 2: Add the "Steal" Button to the Template

Templates live in `templates/` and use the Twig templating language. The project page template is at `templates/Project/ProjectPage.html.twig`.

Open it and look for the section where action buttons are rendered (around line 116-132). You will see patterns like:

```twig
{% if logged_in and my_project %}
  <div class="mt-3" style="display:flex; align-items: center">
    {{ include('Project/NotForKidsButton.html.twig') }}
  </div>
{% endif %}
```

Notice the `logged_in` and `my_project` variables -- these are passed from the controller. Your button should appear when the user is logged in but it is **not** their project.

**Your task:** Add a "Steal" button. You can either:

**Option A:** Add it inline in `ProjectPage.html.twig`:

```twig
{% if logged_in and not my_project %}
  <div class="mt-3">
    <button id="btn-steal-project"
            class="btn btn-danger w-100"
            data-steal-url="{{ path('project_steal', {id: project.id}) }}"
            data-trans-confirm-title="Steal this project?"
            data-trans-confirm-text="This will transfer ownership to you."
            data-trans-success="Project stolen successfully!"
            data-trans-error="Failed to steal project.">
      <i class="material-icons me-1">front_hand</i>
      Steal Project
    </button>
  </div>
{% endif %}
```

**Option B:** Create a separate template file (e.g., `templates/Project/StealButton.html.twig`) and include it, following the pattern used by other button includes.

Key concepts to understand:

- **`{{ path('route_name', {params}) }}`** generates a URL from a route name
- **`data-*` attributes** pass translated strings and configuration from Twig (server) to JavaScript (client). This is the standard pattern throughout the codebase.
- **`{% if condition %}`** controls what is rendered server-side

### Step 3: Add JavaScript to Handle the Button Click

JavaScript files live in `assets/`. The project page entry point is `assets/Project/ProjectPage.js`, which imports and initializes various modules.

Look at how the existing code reads data attributes from the DOM. In `ProjectPage.js`, you will see:

```javascript
const projectElement = document.querySelector('.js-project')
```

And data is accessed like:

```javascript
projectElement.dataset.projectId
projectElement.dataset.loginUrl
```

For making authenticated API calls, the codebase uses the `ApiFetch` helper from `assets/Api/ApiHelper.js`:

```javascript
import { ApiFetch } from '../Api/ApiHelper'

const apiFetch = new ApiFetch('/project/123/steal', 'POST')
apiFetch.generateAuthenticatedFetch().then((response) => {
  if (response.ok) {
    return response.json()
  }
  throw new Error('Request failed')
})
```

`ApiFetch.generateAuthenticatedFetch()` handles the authentication credentials automatically using `credentials: 'same-origin'`.

**Your task:** Add a click handler for the steal button. You can either:

1. Add code directly in `ProjectPage.js` (simplest for learning)
2. Create a new module like `assets/Project/StealProject.js` and import it

Here is a pattern to follow:

```javascript
document.addEventListener('DOMContentLoaded', function () {
  const stealButton = document.getElementById('btn-steal-project')
  if (!stealButton) return

  stealButton.addEventListener('click', function () {
    const url = stealButton.dataset.stealUrl
    const confirmTitle = stealButton.dataset.transConfirmTitle
    const confirmText = stealButton.dataset.transConfirmText

    // Show confirmation dialog, then send the request
    // See Step 5 for the confirmation dialog code
  })
})
```

### Step 4: Implement the Backend Logic

Go back to your controller method from Step 1 and fill in the implementation. Here is the general pattern, based on how `markNotForKids` works:

```php
public function stealProject(string $id): Response
{
    $user = $this->getUser();
    if (!$user instanceof User) {
        return new JsonResponse(
            ['error' => 'You must be logged in'],
            Response::HTTP_UNAUTHORIZED
        );
    }

    $project = $this->project_manager->find($id);
    if (!$project instanceof Program) {
        return new JsonResponse(
            ['error' => 'Project not found'],
            Response::HTTP_NOT_FOUND
        );
    }

    if ($project->getUser() === $user) {
        return new JsonResponse(
            ['error' => 'You already own this project'],
            Response::HTTP_BAD_REQUEST
        );
    }

    // TODO: Change the project's owner
    // TODO: Persist and flush

    return new JsonResponse(['success' => true]);
}
```

Things to figure out on your own:

- How to change the project's owner (look at the `Program` entity for setter methods)
- How to persist the change (look at the `markNotForKids` method for the pattern)

### Step 5: Add User Feedback

The codebase uses two main feedback mechanisms. Try both!

#### Option A: Snackbar (for simple messages)

Snackbars are short, non-blocking messages at the bottom of the screen. They are already included in the base layout (`templates/Layout/Base.html.twig` includes `Layout/Snackbar.html.twig` with id `share-snackbar`).

```javascript
import { showSnackbar } from '../Layout/Snackbar'

// After a successful steal:
showSnackbar('#share-snackbar', 'Project stolen successfully!')
```

The `showSnackbar` function accepts an optional third parameter for duration:

```javascript
import { showSnackbar, SnackbarDuration } from '../Layout/Snackbar'

showSnackbar('#share-snackbar', 'Error!', SnackbarDuration.error) // 10 seconds
```

#### Option B: SweetAlert2 (for confirmation dialogs and rich feedback)

SweetAlert2 is used for confirmation dialogs throughout the project. It is already a dependency (no need to install it).

```javascript
import Swal from 'sweetalert2'

// Confirmation before stealing
Swal.fire({
  title: confirmTitle,
  text: confirmText,
  icon: 'warning',
  showCancelButton: true,
  customClass: {
    confirmButton: 'btn btn-danger',
    cancelButton: 'btn btn-outline-primary',
  },
  buttonsStyling: false,
  confirmButtonText: 'Steal it!',
}).then((result) => {
  if (result.isConfirmed) {
    // Send the steal request here
  }
})
```

Look at the `askForConfirmation` function in `assets/Project/Project.js` (around line 827) for the exact pattern used in the codebase.

**Your task:** Combine confirmation + feedback:

1. When the button is clicked, show a SweetAlert2 confirmation dialog
2. If confirmed, send the POST request using `ApiFetch`
3. On success, show a snackbar and reload the page (so the button disappears)
4. On error, show an error alert

### Step 6: Write a Behat Test

Behat tests are acceptance tests that simulate user interactions. They live in `tests/BehatFeatures/` and are organized by feature area.

Look at an existing test for reference. Open `tests/BehatFeatures/web/project-details/project_details.feature`:

```gherkin
@web @project_page
Feature: As a visitor I want to see a project page

  Background:
    Given there are users:
      | id | name      |
      | 1  | Catrobat  |
      | 2  | Catrobat2 |
    And there are projects:
      | id | name      | owned by |
      | 1  | project 1 | Catrobat |
    And I start a new session

  Scenario: Showing statistics on project page
    Given I am on "/app/project/1"
    And I wait for the page to be loaded
    Then I should see "project 1"
```

Key concepts:

- **`Background`** runs before every scenario (sets up test data)
- **`Given/When/Then`** steps map to PHP step definitions in context classes
- **`there are users:`** and **`there are projects:`** create test fixtures
- **`I log in as "Username"`** authenticates as that user
- **`I am on "/url"`** navigates to a page

**Your task:** Create a new feature file at `tests/BehatFeatures/web/project-details/project_steal.feature`:

```gherkin
@web @project_page
Feature: As a user I want to steal another user's project

  Background:
    Given there are users:
      | id | name     |
      | 1  | Alice    |
      | 2  | Bob      |
    And there are projects:
      | id | name        | owned by |
      | 1  | Cool Game   | Alice    |
    And I start a new session

  Scenario: Steal button should not be visible on my own project
    Given I log in as "Alice"
    And I am on "/app/project/1"
    And I wait for the page to be loaded
    Then the element "#btn-steal-project" should not exist

  Scenario: Steal button should be visible on another user's project
    Given I log in as "Bob"
    And I am on "/app/project/1"
    And I wait for the page to be loaded
    Then the element "#btn-steal-project" should be visible

  Scenario: Stealing a project should change ownership
    Given I log in as "Bob"
    And I am on "/app/project/1"
    And I wait for the page to be loaded
    When I click "#btn-steal-project"
    And I wait for the element ".swal2-confirm" to be visible
    And I click ".swal2-confirm"
    And I wait for AJAX to finish
    # After the page reloads, Bob now owns the project
    # so the steal button should no longer appear
```

**Running the test:**

Always use the `-s` flag to specify the test suite:

```bash
docker exec app.catroweb bin/behat -f pretty -s web-project-details \
  tests/BehatFeatures/web/project-details/project_steal.feature
```

**Important:** After JavaScript changes, you must rebuild and copy assets into the Docker container:

```bash
yarn run dev
docker cp public/build/. app.catroweb:/var/www/catroweb/public/build/
docker exec app.catroweb bin/console cache:clear --env=test
```

## Key Files Reference

| File                                                       | Purpose                                          |
| ---------------------------------------------------------- | ------------------------------------------------ |
| `src/Application/Controller/Project/ProjectController.php` | Project page controller (routes, actions)        |
| `templates/Project/ProjectPage.html.twig`                  | Main project page template                       |
| `assets/Project/ProjectPage.js`                            | JS entry point for the project page              |
| `assets/Project/Project.js`                                | Project interactions (likes, downloads, APK)     |
| `assets/Api/ApiHelper.js`                                  | `ApiFetch` helper for authenticated API calls    |
| `assets/Layout/Snackbar.js`                                | Snackbar notification helper                     |
| `src/DB/Entity/Project/Program.php`                        | Project (Program) Doctrine entity                |
| `src/Project/ProjectManager.php`                           | Service for finding/managing projects            |
| `webpack.config.js`                                        | Webpack Encore config (entry points, build)      |
| `tests/BehatFeatures/web/project-details/`                 | Behat tests for the project page                 |
| `config/routes.yaml`                                       | Route configuration (most routes use attributes) |

## How to Run and Test

### Build assets after JS/CSS changes

```bash
# One-time build
yarn run dev

# Or watch for changes (auto-rebuilds)
yarn run watch
```

### Linting and code style

```bash
# Fix everything at once
yarn run fix

# Or individually:
yarn run fix-js       # ESLint (JavaScript)
yarn run fix-css      # Stylelint (SCSS)
yarn run fix-asset    # Prettier (formatting)
yarn run fix-php      # PHP CS Fixer
```

### PHP static analysis

```bash
# Run from Docker (or natively if available)
docker exec app.catroweb bin/php-cs-fixer fix src/Application/Controller/Project/ProjectController.php
docker exec app.catroweb bin/phpstan analyse src/Application/Controller/Project/ProjectController.php
```

### Running Behat tests

```bash
# Always include -s <suite>
docker exec app.catroweb bin/behat -f pretty -s web-project-details \
  tests/BehatFeatures/web/project-details/project_steal.feature

# Run a specific scenario by line number
docker exec app.catroweb bin/behat -f pretty -s web-project-details \
  "tests/BehatFeatures/web/project-details/project_steal.feature:25"
```

### Debugging

- Browse the test state after a Behat scenario: `http://localhost:8080/index_test.php/`
- Check Symfony logs: `docker exec app.catroweb cat var/log/dev.log | tail -50`
- Clear cache if things seem stale: `docker exec app.catroweb bin/console cache:clear`

## Tips and Common Pitfalls

### 1. Always rebuild assets after JS changes

JavaScript and SCSS changes in `assets/` are not served directly. You must run `yarn run dev` (or have `yarn run watch` running) to compile them into `public/build/`.

### 2. Copy assets into Docker after building

The `public/build/` directory is not volume-mounted into Docker. After building:

```bash
docker cp public/build/. app.catroweb:/var/www/catroweb/public/build/
```

### 3. Clear the Symfony cache

After template or configuration changes, clear the cache:

```bash
docker exec app.catroweb bin/console cache:clear
docker exec app.catroweb bin/console cache:clear --env=test  # for Behat tests
```

### 4. The `data-*` attribute pattern

The codebase passes server-side data (URLs, translations, configuration) to JavaScript via `data-*` attributes on HTML elements. JavaScript reads them with `element.dataset.propertyName`. This keeps JS decoupled from Twig.

### 5. POST requests need the right Content-Type

When sending JSON from JavaScript, always set `Content-Type: application/json`. The `ApiFetch` helper does this automatically when you pass a `data` argument to the constructor.

### 6. Use the `-s` flag with Behat

Without specifying a suite, Behat runs hooks for all 20+ suites even for a single test file. Always use `-s web-project-details` (or whichever suite applies).

### 7. Twig path() and the theme parameter

Routes with a `/{theme}/` prefix (like the main project page) sometimes need an explicit `theme` parameter in `path()`. If your route does not have a theme prefix, you do not need to worry about this.

### 8. Check the browser console

If something is not working, open the browser developer tools (F12) and check the Console tab for JavaScript errors and the Network tab for failed requests.

### 9. The `index_test.php` endpoint

Behat tests use `/index_test.php/` as the entry point. This runs the app in the `test` environment with `debug=false`. If you get 500 errors during testing with no details, add temporary logging in your controller:

```php
file_put_contents('/tmp/debug.txt', $exception->getMessage());
```

### 10. DOMContentLoaded and deferred scripts

Webpack Encore loads scripts with `defer`. Use `document.addEventListener('DOMContentLoaded', ...)` to ensure the DOM is ready before querying elements. This is the standard pattern used throughout the codebase.

## Submitting Your Work

### Git workflow

1. **Create a branch** from `develop`:

   ```bash
   git checkout develop
   git pull origin develop
   git checkout -b feature/steal-project
   ```

2. **Make your changes** and commit with a clear message:

   ```bash
   git add src/Application/Controller/Project/ProjectController.php
   git add templates/Project/ProjectPage.html.twig
   git add assets/Project/ProjectPage.js
   git add tests/BehatFeatures/web/project-details/project_steal.feature
   git commit -m "Add steal project button (introduction task)"
   ```

3. **Run all checks** before pushing:

   ```bash
   yarn run fix          # Auto-fix code style (JS, CSS, PHP)
   yarn run test         # Verify no linting errors remain
   docker exec app.catroweb bin/phpstan analyse src/Application/Controller/Project/ProjectController.php
   ```

4. **Push and create a PR** against `develop`:

   ```bash
   git push -u origin feature/steal-project
   ```

   Then open a Pull Request on GitHub targeting the `develop` branch.

### PR checklist

- [ ] Steal button appears only on other users' projects
- [ ] Confirmation dialog appears before stealing
- [ ] Backend changes project ownership
- [ ] Success feedback is shown to the user
- [ ] Behat test passes
- [ ] `yarn run fix` and `yarn run test` pass
- [ ] PHPStan passes on changed PHP files

Good luck, and have fun! If you get stuck, look at how existing features work -- the comment system and reaction buttons are great examples of the full request lifecycle from button click to database change.
