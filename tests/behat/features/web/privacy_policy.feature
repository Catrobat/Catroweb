@web @security
Feature: Privacy policy feature

  Scenario: Privacy policy url should be visible
    Given  I am on "/app/privacypolicy/"
    And I wait for the page to be loaded
    And I should see "Privacy Policy"

  Scenario: I should be able to decline the privacy policy but will not get an account
    Given I am on "/app/register/"
    And I wait for the page to be loaded
    And I fill in "fos_user_registration_form_username" with "NewUser"
    And I fill in "fos_user_registration_form[email]" with "Newuser@gmail.com"
    And I fill in "fos_user_registration_form_plainPassword_first" with "123456"
    And I fill in "fos_user_registration_form_plainPassword_second" with "123456"
    Then I press "Create an account"
    And I wait for AJAX to finish
    Then I should see a "#termsModal" element
    And I should see "Privacy Policy"
    And I should see "Agree"
    And I should see "Disagree"
    And I click "#disagreeButton"
    Then I wait for AJAX to finish
    Then I should see "Without approval, we can't create an account but the core features are still accessible."

  Scenario: I should be able to accept the privacy policy and register an account
    Given I am on "/app/register/"
    And I wait for the page to be loaded
    And I fill in "fos_user_registration_form_username" with "NewUser"
    And I fill in "fos_user_registration_form[email]" with "Newuser@gmail.com"
    And I fill in "fos_user_registration_form_plainPassword_first" with "123456"
    And I fill in "fos_user_registration_form_plainPassword_second" with "123456"
    Then I press "Create an account"
    And I wait for AJAX to finish
    Then I should see a "#termsModal" element
    And I should see "Privacy Policy"
    And I should see "Agree"
    And I should see "Disagree"
    And I click "#agreeButton"
    Then I wait for the page to be loaded
    Then I should be on "app/register/check-email"
