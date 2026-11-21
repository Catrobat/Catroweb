@web @project_page
Feature: Projects should have descriptions that can be changed by the project owner

  Background:
    Given there are users:
      | id | name      |
      | 1  | Catrobat  |
      | 2  | OtherUser |
    And there are projects:
      | id | name      | owned by  | description    |
      | 1  | project 1 | Catrobat  | my description |
      | 2  | project 2 | OtherUser |                |

  Scenario: Description and credits are in the server HTML, escaped, before any JavaScript runs
    Given there are projects:
      | id | name      | owned by | description        | credit       | scratch_id |
      | 3  | project 3 | Catrobat | <b>not bold</b> hi | thanks to me | 123456     |
    When I GET "/app/project/3"
    Then the response status code should be "200"
    And the client response should contain "&lt;b&gt;not bold&lt;/b&gt; hi"
    And the client response should contain "thanks to me"
    And the client response should contain "https://scratch.mit.edu/projects/123456"
    And the client response should not contain "modulepreload"

  Scenario: A project without description or credits says so in the server HTML
    When I GET "/app/project/2"
    Then the response status code should be "200"
    And the client response should contain "No description available."
    And the client response should contain "No notes and credits available."

  Scenario: A description with a link keeps its line breaks
    Given the project "project 1" has the description:
      """
      first line
      see https://catrobat.org
      last line
      """
    When I go to "/app/project/1"
    And I wait for the page to be loaded
    And I wait for AJAX to finish
    Then the element "#description a[href='https://catrobat.org/']" should exist
    And the element "#description br" should exist
    And I should see "last line"

  Scenario: Changing description is possible if it's my project
    Given I log in as "OtherUser"
    And I go to "/app/project/2"
    And I wait for the page to be loaded
    And I wait for AJAX to finish
    And I wait for the element "#name" to contain "project 2"
    Then the element "#edit-project-button" should be visible
    When I click "#edit-project-button"
    And I wait for the element "#edit-default-button" to be visible
    Then I should see "Default"
    When I click "#edit-default-button"
    And I wait for AJAX to finish
    Then the element "#edit-description-text" should be visible
    When I fill in "edit-description-text" with "This is a new description"
    Then the element "#edit-submit-button" should not be disabled
    When I click "#edit-submit-button"
    And I wait for AJAX to finish
    Then the element "#description" should be visible
    And the element "#edit-text-ui" should not be visible
    And I should see "This is a new description"

  Scenario: Editing description, closing the editor while saving edits
    Given I log in as "Catrobat"
    And I go to "/app/project/1"
    And I wait for the page to be loaded
    And I wait for AJAX to finish
    And I wait for the element "#name" to contain "project 1"
    Then the element "#edit-project-button" should be visible
    When I click "#edit-project-button"
    And I wait for the element "#edit-default-button" to be visible
    Then I should see "Default"
    When I click "#edit-default-button"
    And I wait for AJAX to finish
    Then I fill in "edit-description-text" with "This is a new description"
    And I click "#top-app-bar__back__btn-back"
    And I should see "Do you want to save your changes?"
    When I click ".swal2-confirm"
    And I wait for AJAX to finish
    Then the element "#description" should be visible
    And the element "#edit-text-navigation" should not be visible
    And the element "#edit-text-ui" should not be visible
    And I should see "This is a new description"

  Scenario: Editing description, closing the editor while discarding edits
    Given I log in as "Catrobat"
    And I go to "/app/project/1"
    And I wait for the page to be loaded
    And I wait for AJAX to finish
    And I wait for the element "#name" to contain "project 1"
    Then the element "#edit-project-button" should be visible
    When I click "#edit-project-button"
    And I wait for the element "#edit-default-button" to be visible
    Then I should see "Default"
    When I click "#edit-default-button"
    And I wait for AJAX to finish
    Then I fill in "edit-description-text" with "This is a new description"
    And I click "#top-app-bar__back__btn-back"
    And I should see "Do you want to save your changes?"
    When I click ".swal2-deny"
    Then the element "#edit-text-navigation" should be visible
    When I click "#top-app-bar__back__btn-back"
    Then the element "#description" should be visible
    And the element "#edit-text-navigation" should not be visible
    And the element "#edit-text-ui" should not be visible
    And I should see "my description"

  Scenario: Editing description, closing the editor but going back to unsaved changes
    Given I log in as "Catrobat"
    And I go to "/app/project/1"
    And I wait for the page to be loaded
    And I wait for AJAX to finish
    And I wait for the element "#name" to contain "project 1"
    Then the element "#edit-project-button" should be visible
    When I click "#edit-project-button"
    And I wait for the element "#edit-default-button" to be visible
    Then I should see "Default"
    When I click "#edit-default-button"
    And I wait for AJAX to finish
    Then I fill in "edit-description-text" with "This is a new description"
    And I click "#top-app-bar__back__btn-back"
    And I should see "Do you want to save your changes?"
    When I click ".swal2-close"
    And I wait for AJAX to finish
    Then the element "#edit-description-text" should be visible
    Then the "edit-description-text" field should contain "This is a new description"

  Scenario: Large Project Descriptions are only fully visible when show more was clicked
    Given there are projects with a large description:
      | id | name                     | owned by |
      | 3  | long description project | Catrobat |
    When I go to "/app/project/3"
    And I wait for the page to be loaded
    And I wait for AJAX to finish
    And I wait for the element "#name" to contain "long description project"
    Then I should see "long description project"
    And I should see "MORE INFORMATION"
    When I click "#descriptionShowMoreToggle"
    And I wait for AJAX to finish
    Then I should see "LESS INFORMATION"
    And I should see "the end of the description"

  Scenario: Small Project Descriptions are fully visible
    Given I am on "/app/project/1"
    And I wait for the page to be loaded
    And I wait for AJAX to finish
    And I wait for the element "#description" to contain "my description"
    Then I should see "my description"
    And I should not see "Show more"
