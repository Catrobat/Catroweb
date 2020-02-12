@web @security
Feature: The password field visibility of the registration should be changeable for the user

  Scenario: The password should be hidden as default behaviour
    Given I am on "/app/register/"
    And I wait for the page to be loaded
    And I fill in "fos_user_registration_form_plainPassword_first" with "123456"
    And I fill in "fos_user_registration_form_plainPassword_second" with "223456"
    Then the element "#fos_user_registration_form_plainPassword_first" should have type "password"
    And the element "#fos_user_registration_form_plainPassword_second" should have type "password"
    And the element "#fos_user_registration_form_plainPassword_first" should not have type "text"
    And the element "#fos_user_registration_form_plainPassword_second" should not have type "text"

  Scenario: The visibility of the password should be changeable via a button
    Given I am on "/app/register/"
    And I wait for the page to be loaded
    When I click ".show-hide-password a"
    Then the element "#fos_user_registration_form_plainPassword_first" should have type "text"
    And the element "#fos_user_registration_form_plainPassword_second" should have type "text"
    And the element "#fos_user_registration_form_plainPassword_first" should not have type "password"
    And the element "#fos_user_registration_form_plainPassword_second" should not have type "password"
    When I click ".show-hide-password a"
    Then the element "#fos_user_registration_form_plainPassword_first" should have type "password"
    And the element "#fos_user_registration_form_plainPassword_second" should have type "password"
    And the element "#fos_user_registration_form_plainPassword_first" should not have type "text"
    And the element "#fos_user_registration_form_plainPassword_second" should not have type "text"