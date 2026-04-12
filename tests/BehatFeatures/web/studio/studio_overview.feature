@web @studio
Feature: There is a page to create new studios and also see a list of all available studios

  Background:
    And there are users:
      | id | name        |
      | 1  | StudioAdmin |
      | 2  | Catrobat    |
      | 3  | NewUser     |
    And there are projects:
      | id | name      | owned by |
      | 1  | program 1 | Catrobat |
    And there are studios:
      | id | name             | description     | allow_comments | is_public |
      | 1  | CatrobatStudio01 | hasADescription | true           | true      |
      | 2  | CatrobatStudio02 | hasADescription | true           | false     |
    And there are studio users:
      | id                                   | user        | studio_id | role   |
      | 00000000-0000-0000-0000-000000000001 | StudioAdmin | 1         | admin  |
      | 00000000-0000-0000-0000-000000000002 | Catrobat    | 1         | member |

    And there are studio join requests:
      | User     | Studio           | Status   |
      | Catrobat | CatrobatStudio02 | declined |

  Scenario: A Link in the sidebar is redirecting me to this overview page
    Given I am on the homepage
    When I click "#btn-studio"
    Then I should be on "app/studios"

  Scenario: User is logged in as Admin
    Given I log in as "StudioAdmin"
    And I am on "/app/studios"
    And I wait for the page to be loaded
    And I wait for AJAX to finish
    Then I should see "CatrobatStudio01"
    And the "#studios-user-count-1" element should contain "2"

  Scenario: NewUser is logged in and tries to join a studio
    Given I log in as "NewUser"
    And I am on "/app/studios"
    And I wait for the page to be loaded
    And I wait for AJAX to finish
    And I wait for the element ".studio-join-btn[data-studio-id='1']" to be visible
    When I click ".studio-join-btn[data-studio-id='1']"
    And I wait for AJAX to finish
    Then I wait for the element "#studios-user-count-1" to contain "3"

  Scenario: Catrobat is logged in and tries to leave a studio
    Given I log in as "Catrobat"
    And I am on "/app/studios"
    And I wait for the page to be loaded
    And I wait for AJAX to finish
    And I wait for the element ".studios-list-item-wrapper[data-studio-id='1'] .projects-list-item--menu-btn" to be visible
    When I click ".studios-list-item-wrapper[data-studio-id='1'] .projects-list-item--menu-btn"
    And I click "[data-action='leave'][data-studio-id='1']"
    And I wait 500 milliseconds
    And I click ".swal2-confirm"
    And I wait for AJAX to finish
    Then I wait for the element "#studios-user-count-1" to contain "1"

  Scenario: User is not logged in and clicks the add button
    Given I am on "/app/studios"
    And I wait for the page to be loaded
    And I wait for AJAX to finish
    And the element ".studios-fab" should be visible
    When I click ".studios-fab"
    And I wait for the page to be loaded
    Then I should see "Login"

  Scenario: User is logged in and clicks the add button
    Given I log in as "StudioAdmin"
    And I am on "/app/studios"
    And I wait for the page to be loaded
    And I wait for AJAX to finish
    And the element ".studios-fab" should be visible
    When I click ".studios-fab"
    And I wait for the page to be loaded
    Then I should see "Create studio"

  Scenario: Logged-in user sees My Studios and Explore Studios sections
    Given I log in as "Catrobat"
    And I am on "/app/studios"
    And I wait for the page to be loaded
    And I wait for AJAX to finish
    Then I should see "My Studios"
    And I should see "Explore Studios"

  Scenario: Not-logged-in user sees studios list without My Studios section
    Given I am on "/app/studios"
    And I wait for the page to be loaded
    And I wait for AJAX to finish
    Then I should see "CatrobatStudio01"
    And I should not see "My Studios"

  Scenario: Studio card menu has Open and Share options
    Given I log in as "Catrobat"
    And I am on "/app/studios"
    And I wait for the page to be loaded
    And I wait for AJAX to finish
    And I wait for the element ".studios-list-item-wrapper[data-studio-id='1'] .projects-list-item--menu-btn" to be visible
    When I click ".studios-list-item-wrapper[data-studio-id='1'] .projects-list-item--menu-btn"
    And I wait 500 milliseconds
    Then I should see "Open"
    And I should see "Share"

  Scenario: Create Studio FAB is visible on studios page
    Given I am on "/app/studios"
    And I wait for the page to be loaded
    Then the element ".studios-fab" should be visible

  Scenario: My Studios shows user's studio even when many other studios exist
    Given there are studios:
      | id | name             | description     | allow_comments | is_public |
      | 3  | ExploreStudio01  | explore studio  | true           | true      |
      | 4  | ExploreStudio02  | explore studio  | true           | true      |
      | 5  | ExploreStudio03  | explore studio  | true           | true      |
      | 6  | ExploreStudio04  | explore studio  | true           | true      |
      | 7  | ExploreStudio05  | explore studio  | true           | true      |
      | 8  | ExploreStudio06  | explore studio  | true           | true      |
      | 9  | ExploreStudio07  | explore studio  | true           | true      |
      | 10 | ExploreStudio08  | explore studio  | true           | true      |
      | 11 | ExploreStudio09  | explore studio  | true           | true      |
      | 12 | ExploreStudio10  | explore studio  | true           | true      |
      | 13 | ExploreStudio11  | explore studio  | true           | true      |
      | 14 | ExploreStudio12  | explore studio  | true           | true      |
      | 15 | ExploreStudio13  | explore studio  | true           | true      |
      | 16 | ExploreStudio14  | explore studio  | true           | true      |
      | 17 | ExploreStudio15  | explore studio  | true           | true      |
      | 18 | ExploreStudio16  | explore studio  | true           | true      |
      | 19 | ExploreStudio17  | explore studio  | true           | true      |
      | 20 | ExploreStudio18  | explore studio  | true           | true      |
      | 21 | ExploreStudio19  | explore studio  | true           | true      |
      | 22 | ExploreStudio20  | explore studio  | true           | true      |
      | 23 | ExploreStudio21  | explore studio  | true           | true      |
      | 24 | ExploreStudio22  | explore studio  | true           | true      |
      | 25 | ExploreStudio23  | explore studio  | true           | true      |
      | 26 | ExploreStudio24  | explore studio  | true           | true      |
      | 27 | ExploreStudio25  | explore studio  | true           | true      |
      | 28 | ExploreStudio26  | explore studio  | true           | true      |
      | 29 | ExploreStudio27  | explore studio  | true           | true      |
      | 30 | ExploreStudio28  | explore studio  | true           | true      |
      | 31 | ExploreStudio29  | explore studio  | true           | true      |
      | 32 | ExploreStudio30  | explore studio  | true           | true      |
    And there are studio users:
      | id                                   | user        | studio_id | role  |
      | 00000000-0000-0000-0000-000000000003 | StudioAdmin | 3         | admin |
      | 00000000-0000-0000-0000-000000000004 | StudioAdmin | 4         | admin |
      | 00000000-0000-0000-0000-000000000005 | StudioAdmin | 5         | admin |
      | 00000000-0000-0000-0000-000000000006 | StudioAdmin | 6         | admin |
      | 00000000-0000-0000-0000-000000000007 | StudioAdmin | 7         | admin |
      | 00000000-0000-0000-0000-000000000008 | StudioAdmin | 8         | admin |
      | 00000000-0000-0000-0000-000000000009 | StudioAdmin | 9         | admin |
      | 00000000-0000-0000-0000-000000000010 | StudioAdmin | 10        | admin |
      | 00000000-0000-0000-0000-000000000011 | StudioAdmin | 11        | admin |
      | 00000000-0000-0000-0000-000000000012 | StudioAdmin | 12        | admin |
      | 00000000-0000-0000-0000-000000000013 | StudioAdmin | 13        | admin |
      | 00000000-0000-0000-0000-000000000014 | StudioAdmin | 14        | admin |
      | 00000000-0000-0000-0000-000000000015 | StudioAdmin | 15        | admin |
      | 00000000-0000-0000-0000-000000000016 | StudioAdmin | 16        | admin |
      | 00000000-0000-0000-0000-000000000017 | StudioAdmin | 17        | admin |
      | 00000000-0000-0000-0000-000000000018 | StudioAdmin | 18        | admin |
      | 00000000-0000-0000-0000-000000000019 | StudioAdmin | 19        | admin |
      | 00000000-0000-0000-0000-000000000020 | StudioAdmin | 20        | admin |
      | 00000000-0000-0000-0000-000000000021 | StudioAdmin | 21        | admin |
      | 00000000-0000-0000-0000-000000000022 | StudioAdmin | 22        | admin |
      | 00000000-0000-0000-0000-000000000023 | StudioAdmin | 23        | admin |
      | 00000000-0000-0000-0000-000000000024 | StudioAdmin | 24        | admin |
      | 00000000-0000-0000-0000-000000000025 | StudioAdmin | 25        | admin |
      | 00000000-0000-0000-0000-000000000026 | StudioAdmin | 26        | admin |
      | 00000000-0000-0000-0000-000000000027 | StudioAdmin | 27        | admin |
      | 00000000-0000-0000-0000-000000000028 | StudioAdmin | 28        | admin |
      | 00000000-0000-0000-0000-000000000029 | StudioAdmin | 29        | admin |
      | 00000000-0000-0000-0000-000000000030 | StudioAdmin | 30        | admin |
      | 00000000-0000-0000-0000-000000000031 | StudioAdmin | 31        | admin |
      | 00000000-0000-0000-0000-000000000032 | StudioAdmin | 32        | admin |
    Given I log in as "Catrobat"
    And I am on "/app/studios"
    And I wait for the page to be loaded
    And I wait for AJAX to finish
    # Catrobat is only a member of CatrobatStudio01 (from Background).
    # With 30+ other studios, a single-query approach sorted by creation date
    # would not include it on the first page of 20 results.
    Then I wait for the element "[data-studio--overview-target='myStudios']" to contain "CatrobatStudio01"
