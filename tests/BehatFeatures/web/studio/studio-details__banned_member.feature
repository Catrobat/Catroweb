@web @studio
Feature: A banned member sees that they can't join the studio instead of a Join button

  Background:
    And there are users:
      | id | name         |
      | 1  | StudioAdmin  |
      | 2  | BannedMember |
      | 3  | Newcomer     |
    And there are studios:
      | id | name          | description | allow_comments | is_public |
      | 1  | PublicStudio  | Public      | true           | true      |
      | 2  | PrivateStudio | Private     | true           | false     |
    And there are studio users:
      | id                                   | user         | studio_id | role   | status |
      | 00000000-0000-0000-0000-000000000001 | StudioAdmin  | 1         | admin  | active |
      | 00000000-0000-0000-0000-000000000002 | BannedMember | 1         | member | banned |
      | 00000000-0000-0000-0000-000000000003 | StudioAdmin  | 2         | admin  | active |
      | 00000000-0000-0000-0000-000000000004 | BannedMember | 2         | member | banned |

  Scenario: A banned member sees no Join button on a public studio
    Given I log in as "BannedMember"
    And I am on "/app/studio/1"
    And I wait for the page to be loaded
    And I wait for AJAX to finish
    Then the element ".studio-detail__header__details__banned-note" should be visible
    And I should see "You can't join this studio."
    And the element ".studio-detail__header__details__join-button" should not exist

  Scenario: A banned member sees no Join button on a private studio
    Given I log in as "BannedMember"
    And I am on "/app/studio/2"
    And I wait for the page to be loaded
    And I wait for AJAX to finish
    Then the element ".studio-detail__header__details__banned-note" should be visible
    And I should see "You can't join this studio."
    And the element ".studio-detail__header__details__join-button" should not exist

  Scenario: Other users still see the Join button
    Given I log in as "Newcomer"
    And I am on "/app/studio/1"
    And I wait for the page to be loaded
    And I wait for AJAX to finish
    Then the element ".studio-detail__header__details__join-button" should be visible
    And the element ".studio-detail__header__details__banned-note" should not exist

  Scenario: A banned member sees no Join action in the overview card menu
    Given I log in as "BannedMember"
    And I am on "/app/studios"
    And I wait for the page to be loaded
    And I wait for AJAX to finish
    And I wait for the element ".studios-list-item-wrapper[data-studio-id='1'] .projects-list-item--menu-btn" to be visible
    When I click ".studios-list-item-wrapper[data-studio-id='1'] .projects-list-item--menu-btn"
    Then I wait for the element "[data-action='share'][data-studio-id='1']" to be visible
    And the element "[data-action='join'][data-studio-id='1']" should not exist
