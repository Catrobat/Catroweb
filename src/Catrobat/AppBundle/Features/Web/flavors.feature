@homepage
Feature: Check if flavoring system works

  Background:
    Given there are users:
      | name      | password | token      | email               |
      | Catrobat  | 123456   | cccccccccc | dev1@pocketcode.org |
      | OtherUser | 123456   | dddddddddd | dev2@pocketcode.org |

  Scenario: User views phirocode flavor
    Given I am on "/create@school"
    Then I should see the image "logo_create_at_school_icon.png"

  Scenario: User views phirocode flavor
    Given I am on "/phirocode"
    Then I should see the image "logo_phirocode.png"

  Scenario: User views luna flavor
    Given I am on "/luna"
    Then I should see the image "logo_luna.png"
