@web @project_page
Feature:
  User should see a project image on project pages.
  The owner should be able to change his projects screenshot/thumbnail.

  Background:
    Given there are users:
      | id | name     |
      | 1  | Catrobat |
      | 2  | User1    |

    And there are projects:
      | id | name      | owned by |
      | 1  | project 1 | Catrobat |
      | 2  | project 2 | Catrobat |

  Scenario: The project image should be visible
    Given I am on "/app/project/1"
    And I wait for the page to be loaded
    And I wait for AJAX to finish
    And I wait for the element "#name" to contain "project 1"
    Then I should see "project 1"
    And the element "#project-thumbnail-big" should be visible

  Scenario: Uploading a new image should not work when not logged in
    Given I am on "/app/project/1"
    And I wait for the page to be loaded
    And I wait for AJAX to finish
    And I wait for the element "#name" to contain "project 1"
    Then I should see "project 1"
    But the element "#change-project-thumbnail-button" should not exist

  Scenario: Uploading a new project image should not work when it is not my project
    Given I log in as "User1"
    When I am on "/app/project/1"
    And I wait for the page to be loaded
    And I wait for AJAX to finish
    And I wait for the element "#name" to contain "project 1"
    Then I should see "project 1"
    But the element "#change-project-thumbnail-button" should not exist

  Scenario: Uploading a new image should work when I am logged in and it is my project
    Given I log in as "Catrobat"
    When I am on "/app/project/1"
    And I wait for the page to be loaded
    And I wait for AJAX to finish
    And I wait for the element "#name" to contain "project 1"
    Then I should see "project 1"
    And I wait for the element "#change-project-thumbnail-button" to be visible
    But the element ".text-img-upload-success" should not exist
    When I attach the avatar "logo.png" to "project-screenshot-upload-field"
    Then I wait for the element "#upload-image-spinner" to appear and if so to disappear again
    And I wait for the element "#share-snackbar-label" to contain "Your image was uploaded successfully!"
    When I attach the avatar "galaxy.jpg" to "project-screenshot-upload-field"
    Then I wait for the element "#upload-image-spinner" to appear and if so to disappear again
    And I wait for the element "#share-snackbar-label" to contain "Your image was uploaded successfully!"
