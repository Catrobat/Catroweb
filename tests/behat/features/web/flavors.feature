@web @flavor
Feature: Check if flavoring system works

  Background:
    Given there are users:
      | id | name      |
      | 1  | Catrobat  |
      | 2  | OtherUser |

  Scenario: User views phirocode flavor
    Given I am on "/create@school"
    And I wait for the page to be loaded
    Then I should see the image "logo_create_at_school_icon.png"

  Scenario: User views phirocode flavor
    Given I am on "/phirocode"
    And I wait for the page to be loaded
    Then I should see the image "logo_phirocode.png"

  Scenario: User views luna flavor
    Given I am on "/luna"
    And I wait for the page to be loaded
    Then I should see the image "logo_luna.svg"

  Scenario: User views embroidery flavor
    Given I am on "/embroidery"
    And I wait for the page to be loaded
    Then I should see the image "logo_embroidery.svg"

  Scenario: User views arduino flavor
    Given I am on "/arduino"
    And I wait for the page to be loaded
    Then I should see the image "logo_arduino.png"

  Scenario: User views embroidery flavor
    Given I am on "/embroidery"
    And I wait for the page to be loaded
    Then I should see the image "logo_embroidery.svg"

  Scenario: Viewing details of program 2 using release app
    Given I use a specific "theme/luna" themed app
    And I am on "/app"
    And I wait for the page to be loaded
    Then the logos src should be "logo_luna"
    But the logos src should not be "logo-catroweb"

  Scenario: Viewing details of program 2 using release app
    Given I use a specific "theme/pocketcode" themed app
    And I am on "/app"
    And I wait for the page to be loaded
    Then the logos src should be "logo-catroweb"
    But the logos src should not be "logo_luna"

  Scenario: Viewing details of program 1 using release app
    Given I use a specific "theme/arduino" themed app
    And I am on "/app"
    And I wait for the page to be loaded
    Then the logos src should be "logo_arduino"
    But the logos src should not be "logo_embroidery"
