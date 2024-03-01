@web @studio
Feature: Every Studio should have an overview containing the most necessary information

  Background:
    And there are users:
      | id | name        |
      | 1  | StudioAdmin |
      | 2  | Catrobat    |
      | 3  | Catrobat1    |
    And there are projects:
      | id | name      | owned by |
      | 1  | program 1 | Catrobat |
    And there are studios:
      | id | name             | description     | allow_comments | is_public |
      | 1  | CatrobatStudio01 | hasADescription | true           | true      |
      | 2  | CatrobatStudio02 | hasADescription | true           | false      |
    And there are studio users:
      | id | user        | studio_id | role   |
      | 1  | StudioAdmin | 1         | admin  |
      | 2  | Catrobat    | 1         | member |
      | 5  | StudioAdmin   | 2         | admin |
    And there are studio projects:
      | id | project   | user     | studio_id |
      | 1  | program 1 | Catrobat | 1         |
    And there are studio comments:
      | id | comment     | user     | studio_id |
      | 1  | Cool studio | Catrobat | 1         |
    And there are studio join requests:
      | User      | Studio              | Status   |
      | Catrobat1 | CatrobatStudio02   | declined |


  Scenario: Besides the overview every studio has a project and a comments tab
    Given I log in as "StudioAdmin"
    And I am on "/app/studio/1"
    And I wait for the page to be loaded
    And I should see "CatrobatStudio01"
    And I should see "hasADescription"
    And I should see "public"
    And the ".member_count" element should contain "2"
    And the ".activity_count" element should contain "4"
    Then the ".mdc-tab-bar" element should contain "projects"
    And the ".mdc-tab-bar" element should contain "comments"
    And the element "#projects-pane" should be visible
    And the element "#comments-pane" should not be visible
    When I click "#comments-tab"
    Then the element "#projects-pane" should not be visible
    And the element "#comments-pane" should be visible
    When I click "#projects-tab"
    Then the element "#comments-pane" should not be visible
    And the element "#projects-pane" should be visible


  Scenario:  User not logged in and clicks join button should result to redirect to the login page
    Given I am on "/app/studio/1"
    And I wait for the page to be loaded
    And  the element ".studio-detail__header__details__join-button" should be visible
    When I click ".studio-detail__header__details__join-button"
    And I wait for the page to be loaded
    Then I should see "Login"

  Scenario:  User logged in and clicks join button of a public studio should result in joining the studio
    Given I log in as "Catrobat1"
    And I am on "/app/studio/1"
    And I wait for the page to be loaded
    And  the element ".studio-detail__header__details__join-button" should be visible
    When I click ".studio-detail__header__details__join-button"
    And  I wait for AJAX to finish
    And I wait for the page to be loaded
    Then the ".member_count" element should contain "3"
    And the element ".studio-detail__header__details__leave-button" should be visible

  Scenario:  User logged in and is member of the studio clicks leave button
    Given I log in as "Catrobat"
    And I am on "/app/studio/1"
    And I wait for the page to be loaded
    And  the element ".studio-detail__header__details__leave-button" should be visible
    When I click ".studio-detail__header__details__leave-button"
    And  I wait for AJAX to finish
    And I wait for the page to be loaded
    Then the ".member_count" element should contain "1"
    And the element ".studio-detail__header__details__join-button" should be visible

  Scenario: User logged in and their request was declined form the admin of the private studio
    Given I log in as "Catrobat1"
    And I am on "/app/studio/2"
    And I wait for the page to be loaded
    And  the element ".studio-detail__header__details__declined-button" should be visible

  Scenario: User is logged in and tries to join the the private studio
    Given I log in as "Catrobat"
    And I am on "/app/studio/2"
    And I wait for the page to be loaded
    And  the element ".studio-detail__header__details__join-button" should be visible
    When I click ".studio-detail__header__details__join-button"
    And  I wait for AJAX to finish
    And I wait for the page to be loaded
    Then the ".member_count" element should contain "1"
    And  the element ".studio-detail__header__details__pending-button" should be visible





