@homepage
  Feature:
    As a user, I want to be able to register

  Background:
    Given there are users:
      | name     | password | token       | email               |
      | Catrobat | 123456    | cccccccccc | dev1@pocketcode.org |
      | User1    | 654321    | cccccccccc | dev2@pocketcode.org |

  Scenario: Register, login and logout
    Given I am on homepage
    Then I should see an "#btn-login" element
    When I click "#btn-login"
    Then I should be on "/pocketcode/login"
    When I follow "Create an account"
    Then I should be on "/pocketcode/register"
    And I fill in "sonata_user_registration_form_username" with "CatrobatNew"
    And I fill in "sonata_user_registration_form[email]" with "CatrobatNew@gmail.com"
    And I fill in "sonata_user_registration_form_plainPassword_first" with "123456"
    And I fill in "sonata_user_registration_form_plainPassword_second" with "123456"
    Then I press "Create an account"
    Then I should see a "#termsModal" element
    Then I wait 500 milliseconds
    Then I click "#agreeButton"
    Then I should be on "/pocketcode/myprofile"
    And I should see "CatrobatNew"
    When I am on "/logout"
    Then I should be logged out

  Scenario: Trying to register with different passwords should fail
    Given I am on homepage
    Then I should see an "#btn-login" element
    When I click "#btn-login"
    Then I should be on "/pocketcode/login"
    When I follow "Create an account"
    Then I should be on "/pocketcode/register"
    And I fill in "sonata_user_registration_form_username" with "CatrobatNew"
    And I fill in "sonata_user_registration_form[email]" with "CatrobatNew@gmail.com"
    And I fill in "sonata_user_registration_form_plainPassword_first" with "123456"
    And I fill in "sonata_user_registration_form_plainPassword_second" with "12345"
    Then I press "Create an account"
    Then I should be on "/pocketcode/register"

  Scenario: Trying to register with an existing username should fail
    Given I am on homepage
    Then I should see an "#btn-login" element
    When I click "#btn-login"
    Then I should be on "/pocketcode/login"
    When I follow "Create an account"
    Then I should be on "/pocketcode/register"
    And I fill in "sonata_user_registration_form_username" with "Catrobat"
    And I fill in "sonata_user_registration_form[email]" with "Catrobat@gmail.com"
    And I fill in "sonata_user_registration_form_plainPassword_first" with "123456"
    And I fill in "sonata_user_registration_form_plainPassword_second" with "123456"
    Then I press "Create an account"
    Then I should be on "/pocketcode/register"

  Scenario: Trying to register with an existing e-mail-address should fail
    Given I am on homepage
    Then I should see an "#btn-login" element
    When I click "#btn-login"
    Then I should be on "/pocketcode/login"
    When I follow "Create an account"
    Then I should be on "/pocketcode/register"
    And I fill in "sonata_user_registration_form_username" with "CatrobatNew"
    And I fill in "sonata_user_registration_form[email]" with "dev1@pocketcode.org"
    And I fill in "sonata_user_registration_form_plainPassword_first" with "123456"
    And I fill in "sonata_user_registration_form_plainPassword_second" with "123456"
    Then I press "Create an account"
    Then I should be on "/pocketcode/register"

  Scenario: Trying to register with an e-mail address as username should fail
    Given I am on homepage
    Then I should see an "#btn-login" element
    When I click "#btn-login"
    Then I should be on "/pocketcode/login"
    When I follow "Create an account"
    Then I should be on "/pocketcode/register"
    And I fill in "sonata_user_registration_form_username" with "catro@bat.org"
    And I fill in "sonata_user_registration_form[email]" with "dev1337@pocketcode.org"
    And I fill in "sonata_user_registration_form_plainPassword_first" with "123456"
    And I fill in "sonata_user_registration_form_plainPassword_second" with "123456"
    Then I press "Create an account"
    Then I should be on "/pocketcode/register"


  Scenario: Registering and login with the registered user but wrong password should not work
    Given I am on homepage
    Then I should see an "#btn-login" element
    When I click "#btn-login"
    Then I should be on "/pocketcode/login"
    When I follow "Create an account"
    Then I should be on "/pocketcode/register"
    And I fill in "sonata_user_registration_form_username" with "CatrobatNew"
    And I fill in "sonata_user_registration_form[email]" with "CatrobatNew@gmail.com"
    And I fill in "sonata_user_registration_form_plainPassword_first" with "123456"
    And I fill in "sonata_user_registration_form_plainPassword_second" with "123456"
    Then I press "Create an account"
    Then I should see a "#termsModal" element
    Then I wait 500 milliseconds
    Then I click "#agreeButton"
    Then I should be on "/pocketcode/myprofile"
    When I am on "/logout"
    Then I should be logged out
    When I click "#btn-login"
    And I fill in "username" with "CatrobatNew"
    And I fill in "password" with "12345"
    And I press "Login"
    Then I should see "Your password or username was incorrect."
