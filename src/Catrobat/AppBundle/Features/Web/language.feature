@homepage
Feature: Switch language

  Scenario: Select another language
    Given I am on homepage
    Then the selected language should be "English"
    And I should see "featured"
    But I should not see "Empfohlen"
    Then I switch the language to "Deutsch"
    And I wait 250 milliseconds
    Then the selected language should be "Deutsch"
    And I should see "Empfohlen"
    But I should not see "featured"
