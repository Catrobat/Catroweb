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
    When I follow "Create account"
    And I wait for the page to be loaded
    Then I should be on "/app/register"
    And I fill in "username__input" with "CatrobatNew"
    And I fill in "email__input" with "CatrobatNew@gmail.com"
    And I fill in "password__input" with "123456"
    Then I click "#register-btn"
    And I wait for AJAX to finish
    And I wait 500 milliseconds
    Then I should be on "/app/"

  Scenario: Trying to register with a too short password should fail
    Given I am on "/app/register"
    And I wait for the page to be loaded
    And I fill in "username__input" with "CatrobatNew"
    And I fill in "email__input" with "CatrobatNew@gmail.com"
    And I fill in "password__input" with "12345"
    Then I press "Create account"
    And I wait for the page to be loaded
    Then I should be on "/app/register"
    And I should see "Password too short"

  Scenario: Trying to register with an existing username should fail
    Given there are users:
      | id | name     |
      | 1  | Catrobat |
    When I am on "/app/register"
    And I wait for the page to be loaded
    Then I should be on "/app/register"
    And I fill in "username__input" with "Catrobat"
    And I fill in "email__input" with "Catrobat@gmail.com"
    And I fill in "password__input" with "123456"
    Then I press "Create account"
    And I wait for the page to be loaded
    Then I should be on "/app/register"
    And I should see "Username already in use"

  Scenario: Trying to register with an existing e-mail-address should fail
    Given there are users:
      | id | name     | email               |
      | 1  | Catrobat | dev1@pocketcode.org |
    When I am on "/app/register"
    And I wait for the page to be loaded
    Then I should be on "/app/register"
    And I fill in "username__input" with "CatrobatNew"
    And I fill in "email__input" with "dev1@pocketcode.org"
    And I fill in "password__input" with "123456"
    Then I press "Create account"
    And I wait for the page to be loaded
    Then I should be on "/app/register"
    Then I should be on "/app/register"
    And I should see "Email already in use"

  Scenario: Trying to register with an e-mail address as username should fail
    Given I am on "/app/register"
    And I wait for the page to be loaded
    And I fill in "username__input" with "catro@bat.org"
    And I fill in "email__input" with "dev1337@pocketcode.org"
    And I fill in "password__input" with "123456"
    Then I press "Create account"
    And I wait for the page to be loaded
    Then I should be on "/app/register"
    And I should see "Username must not contain an email address"

  Scenario: Registering should automatically log me in
    Given I am on "/app/register"
    And I wait for the page to be loaded
    And I fill in "username__input" with "CatrobatNew"
    And I fill in "email__input" with "CatrobatNew@gmail.com"
    And I fill in "password__input" with "123456"
    Then I press "Create account"
    And I wait for the page to be loaded
    And I wait 500 milliseconds
    Then I should be on "/app/"
    When I am on "app/user"
    Then the "#top-app-bar__title" element should contain "My Profile"

  Scenario: The password should be hidden as default behaviour
    Given I am on "/app/register"
    And I wait for the page to be loaded
    And I fill in "password__input" with "123456"
    Then the element "#password__input" should have type "password"
    And the element "#password__input" should not have type "text"

  Scenario: The visibility of the password should be changeable via a button
    Given I am on "/app/register"
    And I wait for the page to be loaded
    When I click ".password-toggle"
    Then the element "#password__input" should have type "text"
    And the element "#password__input" should not have type "password"
    When I click ".password-toggle"
    Then the element "#password__input" should have type "password"
    And the element "#password__input" should not have type "text"
