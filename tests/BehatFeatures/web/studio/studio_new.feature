@web @studio
Feature: Create new studio page

  Background:
    And there are users:
      | id | name        |
      | 1  | StudioAdmin |
      | 2  | Catrobat    |

  Scenario: User is not logged in and tries to cancel the create new studio action with the cancel button
    Given I am on "/app/studio/new"
    And I wait for the page to be loaded
    And I should see "Create studio"
    When I click "#studioCreateFormCancel"
    And I wait for the page to be loaded
    Then I should see "Login"

  Scenario: User is not logged in and tries to create a new studio with the create button
    Given I am on "/app/studio/new"
    And I wait for the page to be loaded
    And I should see "Create studio"
    When I click "#studioCreateFormSubmit"
    And I wait for the page to be loaded
    Then I should see "Login"

  Scenario: User is logged in and clicks cancel button
    Given I log in as "StudioAdmin"
    And I am on "/app/studio/new"
    And I wait for the page to be loaded
    Then I should see "Create studio"
    When I click "#studioCreateFormCancel"
    And I wait for the page to be loaded
    Then I should see "Studios"

  Scenario: User is logged in and tries to create a new studio with the create button without user input
    Given I log in as "StudioAdmin"
    And I am on "/app/studios"
    And I wait for the page to be loaded
    And  the element "#add-button" should be visible
    When I click "#add-button"
    And I wait for the page to be loaded
    Then I should see "Create studio"
    When I click "#studioCreateFormSubmit"
    And I wait for the page to be loaded
    Then I should see "Please fill in all required fields."
    And I should see "Please select whether to enable the studio!"
    And I should see "Please select whether the Studio should be private or public!"
    And I should see "Please select whether to allow comments or not in the studio!"

  Scenario:  User is logged in and tries to create a new studio with the create button without user input but with studio name
    Given I log in as "StudioAdmin"
    And I am on "/app/studios"
    And I wait for the page to be loaded
    And  the element "#add-button" should be visible
    When I click "#add-button"
    And I wait for the page to be loaded
    Then I should see "Create studio"
    When  I fill in "name" with "studio"
    And I click "#studioCreateFormSubmit"
    And I wait for the page to be loaded
    Then I should see "Please select whether to enable the studio!"
    And I should see "Please select whether the Studio should be private or public!"
    And I should see "Please select whether to allow comments or not in the studio!"

  Scenario:  User is logged in and tries to create a new studio with the create button  without to check enable studio allow comments
    Given I log in as "StudioAdmin"
    And I am on "/app/studios"
    And I wait for the page to be loaded
    And  the element "#add-button" should be visible
    When I click "#add-button"
    And I wait for the page to be loaded
    Then I should see "Create studio"
    When I fill in "name" with "studio"
    And I select "1" from "form[is_public]"
    And I click "#studioCreateFormSubmit"
    And I wait for the page to be loaded
    Then I should see "Please select whether to enable the studio!"
    And I should see "Please select whether to allow comments or not in the studio!"

  Scenario:  User is logged in and tries to create a new studio with the create button  without to check public studio and allow comments
    Given I log in as "StudioAdmin"
    And I am on "/app/studios"
    And I wait for the page to be loaded
    And  the element "#add-button" should be visible
    When I click "#add-button"
    And I wait for the page to be loaded
    Then I should see "Create studio"
    When I fill in "name" with "studio"
    And I select "1" from "form[is_enabled]"
    And I click "#studioCreateFormSubmit"
    And I wait for the page to be loaded
    Then I should see "Please select whether the Studio should be private or public!"
    And I should see "Please select whether to allow comments or not in the studio!"


  Scenario:  User is logged in and tries to create a new studio with the create button  without to check enable studio and public studio
    Given I log in as "StudioAdmin"
    And I am on "/app/studios"
    And I wait for the page to be loaded
    And  the element "#add-button" should be visible
    When I click "#add-button"
    And I wait for the page to be loaded
    Then I should see "Create studio"
    When I fill in "name" with "studio"
    And I select "1" from "form[allow_comments]"
    And I click "#studioCreateFormSubmit"
    And I wait for the page to be loaded
    Then I should see "Please select whether to enable the studio!"
    And I should see "Please select whether the Studio should be private or public!"

  Scenario:  User is logged in and creates a new studio with the correct data
    Given I log in as "StudioAdmin"
    And I am on "/app/studios"
    And I wait for the page to be loaded
    And  the element "#add-button" should be visible
    When I click "#add-button"
    And I wait for the page to be loaded
    Then I should see "Create studio"
    When I fill in "name" with "studio"
    And I select "1" from "form[allow_comments]"
    And I select "1" from "form[is_enabled]"
    And I select "1" from "form[is_public]"
    And I click "#studioCreateFormSubmit"
    And  I wait for AJAX to finish
    And I wait for the page to be loaded
    Then I should see "Studios"














