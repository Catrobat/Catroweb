@web-general @dataset-language-switcher
Feature: Language switching
  Scenario: Users can switch from English to German
    Given I have accepted cookies
    When I open the "homepage" page
    Then the selected language should be "English"
    And the current page should show "featured"
    And the current page should not show "Empfohlen"
    When I switch the language to "Deutsch"
    Then the selected language should be "Deutsch"
    And the current page should show "Empfohlen"
    And the current page should not show "featured"

  Scenario: Homepage section titles react to language changes
    Given I have accepted cookies
    And the selected language is "English"
    When I open the "homepage" page
    Then the homepage section titles should contain:
      | Most downloaded |
    And the "most downloaded" section should be visible
    When I switch the language to "Russisch"
    Then the homepage section titles should contain:
      | Самые скачиваемые |
    And the "most downloaded" section should be visible
    When I switch the language to "French"
    Then the homepage section titles should contain:
      | Les plus téléchargés |
    And the "most downloaded" section should be visible
    When I switch the language to "Deutsch"
    Then the homepage section titles should contain:
      | heruntergeladen |
    And the "most downloaded" section should be visible

  Scenario: Project details reflect the selected language
    Given I have accepted cookies
    And the selected language is "English"
    When I open the path "/app/project/9601"
    Then the project download button should say "Download"
    When I switch the language to "Russisch"
    Then the project download button should say "Скачать"
