@web @studio
Feature: Create new studio page

  Background:
    And there are users:
      | id | name        |
      | 1  | StudioAdmin |
      | 2  | Catrobat    |
      | 3  | NewUser    |
    And there are programs:
      | id | name      | owned by |
      | 1  | program 1 | Catrobat |
    And there are studios:
      | id | name             | description     | allow_comments | is_public |
      | 1  | CatrobatStudio01 | hasADescription | true           | true      |
    And there are studio users:
      | id | user        | studio_id | role   |
      | 1  | StudioAdmin | 1         | admin  |
      | 2  | Catrobat    | 1         | member |

Scenario: User is logged in as Admin
  Given I log in as "StudioAdmin"
  And I am on "/app/studios"
  And I wait for the page to be loaded
  Then I should see "Joined studios"
  And I should see "Private studios"
  And I should see "Public studios"
  And I should see "CatrobatStudio01"
  And the ".studios-list-item--icons-text" element should contain "2"

Scenario: NewUser is logged in and tries to join a studio
  Given I log in as "NewUser"
  And I am on "/app/studios"
  And I wait for the page to be loaded
  When I click ".mdc-icon-button"
  And  I click ".ajaxRequestJoin"
  And  I wait for AJAX to finish
  And I wait for the page to be loaded
  Then the ".studios-list-item--icons-text" element should contain "3"


  Scenario: Catrobat is logged in and tries to leave a studio
    Given I log in as "Catrobat"
    And I am on "/app/studios"
    And I wait for the page to be loaded
    When I click ".mdc-icon-button"
    And  I click ".ajaxRequestLeave"
    And  I wait for AJAX to finish
    And I wait for the page to be loaded
    Then the ".studios-list-item--icons-text" element should contain "1"



Scenario: User is not logged in and clicks the add button
  Given I am on "/app/studios"
  And I wait for the page to be loaded
  And  the element "#add-button" should be visible
  When I click "#add-button"
  And I wait for the page to be loaded
  Then I should see "Login"

Scenario: User is logged in and clicks the add button
  Given I log in as "StudioAdmin"
  And I am on "/app/studios"
  And I wait for the page to be loaded
  And  the element "#add-button" should be visible
  When I click "#add-button"
  And I wait for the page to be loaded
  Then I should see "Create studio"