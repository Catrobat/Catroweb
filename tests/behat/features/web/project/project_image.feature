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
    Then I should see "project 1"
    And the element "#project-thumbnail-big" should be visible

  Scenario: Uploading a new image should not work when not logged in
    Given I am on "/app/project/1"
    And I wait for the page to be loaded
    Then I should see "project 1"
    But the element "#change-project-thumbnail-button" should not exist

  Scenario: Uploading a new project image should not work when it is not my project
    Given I log in as "User1"
    When I am on "/app/project/1"
    And I wait for the page to be loaded
    Then I should see "project 1"
    But the element "#change-project-thumbnail-button" should not exist

  Scenario: Uploading a new image should work when I am logged in and it is my project
    Given I log in as "Catrobat"
    When I am on "/app/project/1"
    And I wait for the page to be loaded
    Then I should see "project 1"
    When I click "#change-project-thumbnail-button"
    And I wait for AJAX to finish
    When I attach the avatar "logo.png" to "file"
    And I wait for the element ".text-img-upload-success" to be visible
    Then I wait for the element ".text-img-upload-success" to contain "Your image was uploaded successfully!"
    When I click "#change-project-thumbnail-button"
    And I wait for AJAX to finish
    When I attach the avatar "galaxy.jpg" to "file"
    And I wait for the element ".text-img-upload-success" to be visible
    Then I wait for the element ".text-img-upload-success" to contain "Your image was uploaded successfully!"
