@admin
Feature: Surveys can be added, removed, and updated in the Admin interface


  Background:
    Given there are admins:
      | name  | password |
      | Admin | 123456   |
    And there are surveys:
      | language code | url                |
      | en            | www.catrosurvey.at |

  Scenario: List should be complete
    Given I log in as "Admin" with the password "123456"
    And I am on "/admin/all_surveys/list"
    And I wait for the page to be loaded
    Then I should see the survey table:
      | Language Code | Url                | Active |
      | English       | www.catrosurvey.at | yes    |

  Scenario: I can add new surveys
    Given I log in as "Admin" with the password "123456"
    And I am on "/admin/all_surveys/create"
    And I wait for the page to be loaded
    Then I should see "Language Code"
    Then I should see "Url"
    Then I should see "Active"
    Then I should see "Create"

  Scenario: I can delete entries
    Given I log in as "Admin" with the password "123456"
    And I am on "/admin/all_surveys/list"
    And I wait for the page to be loaded
    Then I should see the survey table:
      | Language Code | Url                | Active |
      | English       | www.catrosurvey.at | yes    |
    When I go to "/admin/all_surveys/1/delete"
    And I wait for the page to be loaded
    And I click ".btn-danger"
    And I wait for the page to be loaded
    And I am on "/admin/all_surveys/list"
    And I wait for the page to be loaded
    Then I should not see "www.catrosurvey.at"
