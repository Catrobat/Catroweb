@web @studio
Feature: A studio has a comment section

  Background:
    And there are users:
      | id | name        |
      | 1  | StudioAdmin |
      | 2  | StudioUser  |
      | 3  | Guest       |

    And there are studios:
      | id | name             | description     | allow_comments | is_public |
      | 1  | CatrobatStudio01 | hasADescription | true           | true      |
      | 2  | NoComments       | noComments      | false          | true      |

    And there are studio users:
      | id | user        | studio_id | role   |
      | 1  | StudioAdmin | 1         | admin  |
      | 2  | StudioUser  | 1         | member |
      | 3  | StudioAdmin | 2         | admin  |

  Scenario: Non-logged-in user sees comments tab but cannot post
    Given I am on "/app/studio/1"
    And I wait for the page to be loaded
    When I click "#comments-tab"
    And I wait for AJAX to finish
    Then the element "#comments-pane" should be visible
    And the element "[data-studio--comment-target='form']" should not exist

  Scenario: Member posts a comment
    Given I log in as "StudioUser"
    And I am on "/app/studio/1"
    And I wait for the page to be loaded
    When I click "#comments-tab"
    And I wait for AJAX to finish
    And I fill in the element "[data-studio--comment-target='messageInput']" with "Hello studio!"
    And I click "[data-action='click->studio--comment#postComment']"
    And I wait for AJAX to finish
    Then I wait for the element "[data-studio--comment-target='container']" to contain "Hello studio!"

  Scenario: Admin deletes a comment
    Given I log in as "StudioAdmin"
    And I am on "/app/studio/1"
    And I wait for the page to be loaded
    When I click "#comments-tab"
    And I wait for AJAX to finish
    And I fill in the element "[data-studio--comment-target='messageInput']" with "Delete me"
    And I click "[data-action='click->studio--comment#postComment']"
    And I wait for AJAX to finish
    And I wait for the element "[data-studio--comment-target='container']" to contain "Delete me"
    When I click ".comment-delete-button"
    And I wait 500 milliseconds
    And I click ".swal2-confirm"
    And I wait for AJAX to finish
    Then the element "[data-studio--comment-target='container']" should not contain "Delete me"

  Scenario: Comments disabled shows message
    Given I am on "/app/studio/2"
    And I wait for the page to be loaded
    When I click "#comments-tab"
    And I wait 500 milliseconds
    Then the element "[data-studio--comment-target='disabledComments']" should be visible

  Scenario: Empty comment is rejected
    Given I log in as "StudioUser"
    And I am on "/app/studio/1"
    And I wait for the page to be loaded
    When I click "#comments-tab"
    And I wait for AJAX to finish
    And I fill in the element "[data-studio--comment-target='messageInput']" with "   "
    And I click "[data-action='click->studio--comment#postComment']"
    And I wait 500 milliseconds
    Then the element "[data-studio--comment-target='noComments']" should be visible
