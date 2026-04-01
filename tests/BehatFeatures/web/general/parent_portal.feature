@web
Feature: Parent Portal

  Background:
    Given there are users:
      | id | name       | password | email              | date_of_birth | is_minor | consent_status | parent_email       | verified |
      | 1  | ChildOne   | 123456   | child1@catrobat.at | 2018-06-15    | true     | granted        | parent@catrobat.at | true     |
      | 2  | ChildTwo   | 123456   | child2@catrobat.at | 2019-01-10    | true     | pending        | parent@catrobat.at | true     |
      | 3  | AdultUsr   | 123456   | adult@catrobat.at  | 2000-01-15    | false    | not_required   |                    | true     |
      | 4  | Unverified | 123456   | unver@catrobat.at  | 2000-01-15    | false    | not_required   |                    | false    |

  Scenario: Parent info page is accessible
    Given I am on "/app/parent-info"
    And I wait for the page to be loaded
    Then the response should contain "Information for Parents"

  Scenario: Parent portal page is accessible
    Given I am on "/app/parent"
    And I wait for the page to be loaded
    Then the response should contain "Parent Portal"

  Scenario: Parent portal send link requires valid email
    Given I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
    """
      {
        "email": "",
        "captcha_token": ""
      }
    """
    And I request "POST" "/app/parent/send-link"
    Then the response status code should be "422"

  Scenario: Parent management page requires valid signed link
    Given I am on "/app/parent/manage?email=parent@catrobat.at"
    And I wait for the page to be loaded
    Then the response should contain "invalid or has expired"

  Scenario: Footer contains parent info link
    Given I am on homepage
    And I wait for the page to be loaded
    Then the response should contain "Parents"

  Scenario: Unverified user is redirected to verify-pending
    Given I log in as "Unverified" with the password "123456"
    And I am on "/app/"
    And I wait for the page to be loaded
    Then I should be on "/app/verify-pending"

  Scenario: Consent-pending user is redirected to consent-pending page
    Given I log in as "ChildTwo" with the password "123456"
    And I am on "/app/"
    And I wait for the page to be loaded
    Then I should be on "/app/consent-pending"
