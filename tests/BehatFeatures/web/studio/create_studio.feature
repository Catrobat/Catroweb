@web @studio
Feature: There is a dedicated page to create a new studio

  Background:
    And there are users:
      | id | name        | password |
      | 1  | StudioAdmin | 123456   |
      | 2  | Catrobat    | 123456   |

  Scenario: If I am not logged in, I will be redirected to the login page
    Given I am on "/app/studio/create"
    Then I should be on "/app/login"

  Scenario: Canceling the create action brings me back to the overview of all studios
    Given I log in as "Catrobat"
    When I am on "/app/studio/create"
    And I wait for the page to be loaded
    And I should see "Create studio"
    When I click "#top-app-bar__back__btn-back"
    And I wait for the page to be loaded
    Then I should be on "/app/studios"

  Scenario: Just clicking submit should do nothing and show the html required field validation
    Given I log in as "Catrobat"
    When I am on "/app/studio/create"
    And I wait for the page to be loaded
    When I click "#top-app-bar__btn-save"
    And I wait 500 milliseconds
    Then I should be on "/app/studio/create"

  Scenario: Using invalid values should show the validation errors
    Given I log in as "Catrobat"
    When I am on "/app/studio/create"
    And I wait for the page to be loaded
    When  I fill in "name" with "-"
    And  I fill in "description" with "more than 3000: ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------"
    And I should not see "Name too short"
    And I should not see "Description too long"
    When I click "#top-app-bar__btn-save"
    And I wait 500 milliseconds
    Then I should be on "/app/studio/create"
    And I should see "Name too short"
    And I should see "Description too long"

  Scenario: Creating a new studio should work with just the name and description, the other properties have default values
    Given I log in as "Catrobat"
    When I am on "/app/studio/create"
    And I wait for the page to be loaded
    When  I fill in "name" with "My random studio"
    And  I fill in "description" with "with a specific description"
    When I click "#top-app-bar__btn-save"
    And I wait 500 milliseconds
    Then the studio with the name "My random studio" should exist with following values:
      | key             | value                       |
      | description     | with a specific description |
      | is_enabled      | true                        |
      | is_public       | true                        |
      | enable_comments | true                        |
      | cover_path      |                             |
    And the element ".studio-detail__header" should be visible
    And I should see "My random studio"
    And I should see "with a specific description"

  Scenario: Creating a new studio should allows me to fully configure my studio
    Given I log in as "Catrobat"
    When I am on "/app/studio/create"
    And I wait for the page to be loaded
    When  I fill in "name" with "My random studio"
    And  I fill in "description" with "with a specific description"
    # toggle switches
    And  I click "#studio-is-public"
    And  I click "#studio-enable-comments"
    # image
    And I attach the avatar "logo.png" to "studio-file-input"
    When I click "#top-app-bar__btn-save"
    And I wait 500 milliseconds
    Then the studio with the name "My random studio" should exist with following values:
      | key             | value                       |
      | description     | with a specific description |
      | is_enabled      | true                        |
      | is_public       | false                       |
      | enable_comments | false                       |
      | cover_path      | My-random-studio.png        |
    And the element ".studio-detail__header" should be visible
    And I should see "My random studio"
    And I should see "with a specific description"
