@web @security
Feature:
  As a user, I want to be able to register

  Scenario: Register via the web should work
    Given I am on homepage
    And I wait for the page to be loaded
    Then I should see an "#btn-login" element
    When I click "#btn-login"
    And I wait for the page to be loaded
    Then I should be on "/app/login"
    When I follow "Create an account"
    And I wait for the page to be loaded
    Then I should be on "/app/register/"
    And I fill in "fos_user_registration_form_username" with "CatrobatNew"
    And I fill in "fos_user_registration_form[email]" with "CatrobatNew@gmail.com"
    And I fill in "fos_user_registration_form_plainPassword_first" with "123456"
    And I fill in "fos_user_registration_form_plainPassword_second" with "123456"
    Then I press "Create an account"
    Then I should see a "#termsModal" element
    Then I wait for AJAX to finish
    Then I click "#agreeButton"
    And I wait for the page to be loaded
    Then I should be on "/app/register/check-email"

  Scenario: Trying to register with different passwords should fail
    Given I am on "/app/register/"
    And I wait for the page to be loaded
    And I fill in "fos_user_registration_form_username" with "CatrobatNew"
    And I fill in "fos_user_registration_form[email]" with "CatrobatNew@gmail.com"
    And I fill in "fos_user_registration_form_plainPassword_first" with "123456"
    And I fill in "fos_user_registration_form_plainPassword_second" with "123457"
    Then I press "Create an account"
    Then I wait for AJAX to finish
    Then I should see a "#termsModal" element
    Then I click "#agreeButton"
    And I wait for the page to be loaded
    Then I should be on "/app/register/"
    And I should see "The entered passwords don't match."

  Scenario: Trying to register with a too short password should fail
    Given I am on "/app/register/"
    And I wait for the page to be loaded
    And I fill in "fos_user_registration_form_username" with "CatrobatNew"
    And I fill in "fos_user_registration_form[email]" with "CatrobatNew@gmail.com"
    And I fill in "fos_user_registration_form_plainPassword_first" with "12345"
    And I fill in "fos_user_registration_form_plainPassword_second" with "12345"
    Then I press "Create an account"
    Then I should see a "#termsModal" element
    Then I wait for AJAX to finish
    Then I click "#agreeButton"
    And I wait for the page to be loaded
    Then I should be on "/app/register/"
    And I should see "The password is too short"

  Scenario: Trying to register with an existing username should fail
    Given there are users:
      | id | name     |
      | 1  | Catrobat |
    When I am on "/app/register/"
    And I wait for the page to be loaded
    Then I should be on "/app/register/"
    And I fill in "fos_user_registration_form_username" with "Catrobat"
    And I fill in "fos_user_registration_form[email]" with "Catrobat@gmail.com"
    And I fill in "fos_user_registration_form_plainPassword_first" with "123456"
    And I fill in "fos_user_registration_form_plainPassword_second" with "123456"
    Then I press "Create an account"
    And I wait for AJAX to finish
    Then I should see a "#termsModal" element
    When I click "#agreeButton"
    And I wait for the page to be loaded
    Then I should be on "/app/register/"

  Scenario: Trying to register with an existing e-mail-address should fail
    Given there are users:
      | id | name     | email               |
      | 1  | Catrobat | dev1@pocketcode.org |
    When I am on "/app/register/"
    And I wait for the page to be loaded
    Then I should be on "/app/register/"
    And I fill in "fos_user_registration_form_username" with "CatrobatNew"
    And I fill in "fos_user_registration_form[email]" with "dev1@pocketcode.org"
    And I fill in "fos_user_registration_form_plainPassword_first" with "123456"
    And I fill in "fos_user_registration_form_plainPassword_second" with "123456"
    Then I press "Create an account"
    And I wait for AJAX to finish
    Then I should see a "#termsModal" element
    When I click "#agreeButton"
    And I wait for the page to be loaded
    Then I should be on "/app/register/"

  Scenario: Trying to register with an e-mail address as username should fail
    Given I am on "/app/register/"
    And I wait for the page to be loaded
    And I fill in "fos_user_registration_form_username" with "catro@bat.org"
    And I fill in "fos_user_registration_form[email]" with "dev1337@pocketcode.org"
    And I fill in "fos_user_registration_form_plainPassword_first" with "123456"
    And I fill in "fos_user_registration_form_plainPassword_second" with "123456"
    Then I press "Create an account"
    And I wait for AJAX to finish
    Then I should see a "#termsModal" element
    When I click "#agreeButton"
    And I wait for the page to be loaded
    Then I should be on "/app/register/check-email"

  Scenario: Registering and login with the registered user but wrong password should not work
    Given I am on "/app/register/"
    And I wait for the page to be loaded
    And I fill in "fos_user_registration_form_username" with "CatrobatNew"
    And I fill in "fos_user_registration_form[email]" with "CatrobatNew@gmail.com"
    And I fill in "fos_user_registration_form_plainPassword_first" with "123456"
    And I fill in "fos_user_registration_form_plainPassword_second" with "123456"
    Then I press "Create an account"
    And I wait for AJAX to finish
    Then I should see a "#termsModal" element
    When I click "#agreeButton"
    And I wait for the page to be loaded
    Then I should be on "/app/register/check-email"
    When I am on "/app/logout"
    And I wait for the page to be loaded
    Then I should be logged out
    When I click "#btn-login"
    And I wait for the page to be loaded
    And I fill in "username" with "CatrobatNew"
    And I fill in "password" with "12345"
    And I press "Login"
    And I wait for the page to be loaded
    Then I should see "Your password or username was incorrect."

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