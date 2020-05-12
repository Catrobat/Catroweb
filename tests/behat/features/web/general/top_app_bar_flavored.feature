@web @flavor
Feature: Check if flavoring system works

  Scenario: User views phirocode flavor
    Given I am on "/create@school"
    And I wait for the page to be loaded
    Then the "#top-app-bar__title" element should contain "Create@School"

  Scenario: User views phirocode flavor
    Given I am on "/phirocode"
    And I wait for the page to be loaded
    Then the "#top-app-bar__title" element should contain "Phirocode"

  Scenario: User views luna flavor
    Given I am on "/luna"
    And I wait for the page to be loaded
    Then the "#top-app-bar__title" element should contain "Luna &amp; Cat"

  Scenario: User views embroidery flavor
    Given I am on "/embroidery"
    And I wait for the page to be loaded
    Then the "#top-app-bar__title" element should contain "Embroidery Designer"

  Scenario: User views arduino flavor
    Given I am on "/arduino"
    And I wait for the page to be loaded
    Then the "#top-app-bar__title" element should contain "Arduino Code"

  Scenario: User views embroidery flavor
    Given I am on "/embroidery"
    And I wait for the page to be loaded
    Then the "#top-app-bar__title" element should contain "Embroidery Designer"

  Scenario: Viewing details of program 2 using release app
    Given I use a specific "theme/luna" themed app
    And I am on "/app"
    And I wait for the page to be loaded
    Then the "#top-app-bar__title" element should contain "Luna &amp; Cat"
    Then the "#top-app-bar__title" element should not contain "Pocket Code"

  Scenario: Viewing details of program 2 using release app
    Given I use a specific "theme/pocketcode" themed app
    And I am on "/app"
    And I wait for the page to be loaded
    Then the "#top-app-bar__title" element should contain "Pocket Code"

  Scenario: Viewing details of program 1 using release app
    Given I use a specific "theme/arduino" themed app
    And I am on "/app"
    And I wait for the page to be loaded
    Then the "#top-app-bar__title" element should contain "Arduino Code"
    Then the "#top-app-bar__title" element should not contain "Pocket Code"
