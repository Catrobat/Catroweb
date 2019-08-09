@homepage
Feature: Check if flavoring system works

  Background:
    Given there are users:
      | name      | password | token      | email               | id |
      | Catrobat  | 123456   | cccccccccc | dev1@pocketcode.org |  1 |
      | OtherUser | 123456   | dddddddddd | dev2@pocketcode.org |  2 |

  Scenario: User views phirocode flavor
    Given I am on "/create@school"
    Then I should see the image "logo_create_at_school_icon.png"

  Scenario: User views phirocode flavor
    Given I am on "/phirocode"
    Then I should see the image "logo_phirocode.png"

  Scenario: User views luna flavor
    Given I am on "/luna"
    Then I should see the image "logo_luna.png"

  Scenario: Viewing details of program 2 using release app
    Given I use a specific "theme/luna" themed app
    And I am on "/app"
    Then the logos src should be "logo_luna"
    And the logos src should not be "logo-catroweb"

  Scenario: Viewing details of program 2 using release app
    Given I use a specific "theme/pocketcode" themed app
    And I am on "/app"
    Then the logos src should not be "logo_luna"
    And the logos src should be "logo-catroweb"
