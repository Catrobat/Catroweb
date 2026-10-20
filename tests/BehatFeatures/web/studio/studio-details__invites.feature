@web @studio
Feature: Studio admins hand out invite codes, pupils join private studios with them

  Background:
    Given there are users:
      | id | name        |
      | 1  | StudioAdmin |
      | 2  | Pupil       |
      | 3  | Banned      |
    And there are studios:
      | id | name        | description | is_public |
      | 1  | ClassStudio | Our class   | false     |
    And there are studio users:
      | id                                   | user        | studio_id | role   | status |
      | 00000000-0000-0000-0000-000000000001 | StudioAdmin | 1         | admin  | active |
      | 00000000-0000-0000-0000-000000000002 | Banned      | 1         | member | banned |
    And there are studio invites:
      | studio_name | code       |
      | ClassStudio | CLASS1CODE |

  Scenario: An admin creates an invite code in the studio settings
    Given I log in as "StudioAdmin"
    And I am on "/app/studio/1"
    And I wait for the page to be loaded
    And I wait for AJAX to finish
    When I click "#top-app-bar__btn-options"
    And I wait 500 milliseconds
    And I click "#top-app-bar__btn-edit-studio"
    And I wait for the element "#studio-admin-settings-modal" to be visible
    And I wait for AJAX to finish
    Then the element "#studio-invite-list" should contain "CLASS1CODE"
    When I click "#studio-invite-create"
    And I wait for AJAX to finish
    Then I should see 2 ".studio-invite"
    And the element "#studio-invite-list" should contain "/studio/join/"

  Scenario: Opening an invite link while logged out leads to the login page
    Given I am on "/app/studio/join/CLASS1CODE"
    Then I should be on "/app/login"

  Scenario: A pupil joins the private studio through an invite link
    Given I log in as "Pupil"
    And I am on "/app/studio/join/CLASS1CODE"
    And I wait for the page to be loaded
    When I click "#studio-join-with-code-submit"
    And I wait for the page to be loaded
    And I wait for AJAX to finish
    Then I should be on "/app/studio/1"
    And I should see "ClassStudio"

  Scenario: A wrong code shows an error
    Given I log in as "Pupil"
    And I am on "/app/studio/join/WRONGCODE1"
    And I wait for the page to be loaded
    When I click "#studio-join-with-code-submit"
    And I wait for AJAX to finish
    Then I should be on "/app/studio/join/WRONGCODE1"
    And the element "#studio-join-with-code-error" should be visible

  Scenario: A banned member cannot get back in with a code
    Given I log in as "Banned"
    And I am on "/app/studio/join/CLASS1CODE"
    And I wait for the page to be loaded
    When I click "#studio-join-with-code-submit"
    And I wait for AJAX to finish
    Then the element "#studio-join-with-code-error" should be visible
    And I should see "You have been banned from this studio."

  Scenario: Logged-in users find the code entry on the studios overview
    Given I log in as "Pupil"
    And I am on "/app/studios"
    And I wait for the page to be loaded
    When I click "#studios-join-with-code"
    And I wait for the page to be loaded
    Then I should be on "/app/studio/join"
