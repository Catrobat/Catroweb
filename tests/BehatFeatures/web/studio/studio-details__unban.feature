@web @studio
Feature: Studio admins remove bans without adding people back

  Background:
    Given there are users:
      | id | name         |
      | 1  | StudioAdmin  |
      | 2  | BannedMember |
      | 3  | Member       |
    And there are studios:
      | id | name   | is_public |
      | 1  | Studio | true      |
    And there are studio users:
      | id | user         | studio_id | role   | status |
      | 1  | StudioAdmin  | 1         | admin  | active |
      | 2  | BannedMember | 1         | member | banned |
      | 3  | Member       | 1         | member | active |

  Scenario: An admin can remove a ban from the members dialog
    Given I log in as "StudioAdmin"
    And I am on "/app/studio/1"
    And I wait for the page to be loaded
    And I wait for AJAX to finish
    When I click ".studio-detail__header__details__button--member"
    Then I wait for the element ".studio-banned-member" to contain "BannedMember"
    And I should see "Banned members"
    And I should see "It does not add them back to the studio."
    When I click ".studio-banned-member button"
    And I wait for AJAX to finish
    Then the element ".studio-banned-member" should not exist
    And I should see 2 ".member__list-entry"
    When I am on "/app/studio/1"
    And I wait for the page to be loaded
    And I wait for AJAX to finish
    Then the ".member_count" element should contain "2"

  Scenario: A regular member sees no ban management
    Given I log in as "Member"
    And I am on "/app/studio/1"
    And I wait for the page to be loaded
    And I wait for AJAX to finish
    When I click ".studio-detail__header__details__button--member"
    And I wait for AJAX to finish
    Then the element "[data-studio--member-list-target='bannedSection']" should not exist
    And I should not see "BannedMember"
