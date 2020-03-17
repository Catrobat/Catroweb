@web
Feature: Pocketcode homepage
  In order to access and browse the projects
  As a visitor
  I want to be able to see the homepage

  Background:
    Given there are users:
      | id | name     |
      | 1  | Catrobat |
      | 2  | User1    |
      | 3  | Catrobat2|

    And there are projects:
      | id | name      | owned by |
      | 1  | project 1 | Catrobat |
      | 2  | project 2 | Catrobat |
      | 3  | project 3 | User1    |
      | 4  | project 4 | User1    |
      | 5  | project 5 | User1    |
      | 6  | project 6 | Catrobat2|
      | 7  | project 7 | Catrobat2|

    And following projects are featured:
      | name      | url                   | active | priority |
      | project 1 |                       | 0      | 1        |
      | project 2 |                       | 1      | 3        |
      | project 3 |                       | 1      | 2        |
      |           | http://www.google.at/ | 1      | 5        |
      |           | http://www.orf.at/    | 0      | 4        |

    Given there are Scratch remix relations:
      | scratch_parent_id | catrobat_child_id |
      | 70058680          | 6                 |
      | 70058680          | 7                 |

  Scenario: Scratch remixes project should be visible:
    Given I am on homepage
    And I wait for the page to be loaded
    Then I should see the featured slider
    Then the element "#scratchRemixes" should exist
    And the "#scratchRemixes" element should contain "project 6"
    And the "#scratchRemixes" element should contain "project 7"
    And the "#scratchRemixes" element should not contain "project 1"

  Scenario: Viewing the homepage at website root
    Given I am on homepage
    And I wait for the page to be loaded
    Then I should see the featured slider
    Then the element "#newest" should exist
    Then the element "#recommended" should exist
    Then the element "#mostDownloaded" should exist
    Then the element "#random" should exist
    Then the element "#scratchRemixes" should exist
    Then the element "#mostViewed" should exist

  Scenario: Welcome Section
    Given I am on homepage
    And I wait for the page to be loaded
    Then I should see the welcome section
    And I should see the video available at "https://www.youtube.com/embed/BHe2r2WU-T8"
    And I should see "Get it on Google Play"
    And I should see "Get it on IOS"

  Scenario: Cant see the Welcome Section when logged in
    Given I log in as "Catrobat"
    And I go to the homepage
    And I wait for the page to be loaded
    Then I should not see the welcome section

  Scenario: Featured Programs and Urls
    Given I am on homepage
    And I wait for the page to be loaded
    Then I should see the featured slider
    And I should see the slider with the values "http://www.google.at/,project 2,project 3"