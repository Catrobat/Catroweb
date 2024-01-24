@web @profile_page
Feature:
  In order to manage the my profile page
  As a logged in user
  I want to be able to change my password, email and upload an avatar image and delete my own projects

  Background:
    Given there are users:
      | id | name     |
      | 1  | Catrobat |
      | 2  | User1    |
    And there are projects:
      | id | name      | description | owned by | downloads | apk_downloads | views | upload time      | version | language version | private |
      | 1  | project 1 | p1          | Catrobat | 3         | 2             | 12    | 01.01.2013 12:00 | 0.8.5   | 0.6              | false   |
      | 2  | project 2 |             | Catrobat | 333       | 123           | 9     | 22.04.2014 13:00 | 0.8.5   | 999              | true    |
      | 3  | project 3 |             | User1    | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   | 0.6              | false   |

    And I log in as "Catrobat"
    And I am on "/app/user"
    And I wait for the page to be loaded
    Then I should see "My Profile"

  Scenario: changing my username must work
    Given I click "#top-app-bar__btn-settings"
    And I wait for the element "#user-settings-modal" to be visible
    Then I should see "User Settings"
    And I click ".profile__user-settings .nav-link[data-bs-target='#profile-settings-modal']"
    And I wait for the element "#profile-settings-modal" to be visible
    Then I should see "Profile Settings"
    And the element "#profile-username__input" should be visible
    When I fill in "username" with "Mr.Catro"
    And I click "#profile_settings-save_action"
    And I wait for the page to be loaded
    Then I should be on "/app/user"
    And I should see "Your profile has been successfully changed."
    And the ".profile__basic-info__text__name" element should contain "Mr.Catro"
    When I go to "/app/logout"
    And I wait for the page to be loaded
    And I log in as "Catrobat"
    Then I should see "Your password or username was incorrect."
    When I try to log in as "Mr.Catro"
    Then I should be logged in

  Scenario: When changing the username the min length must be 3
    Given I click "#top-app-bar__btn-settings"
    And I wait for the element "#user-settings-modal" to be visible
    And I click ".profile__user-settings .nav-link[data-bs-target='#profile-settings-modal']"
    And I wait for the element "#profile-settings-modal" to be visible
    When I fill in "username" with "Mr"
    And I click "#profile_settings-save_action"
    And I wait for the page to be loaded
    And I should see "Username too short"
    When I go to "/app/logout"
    And I wait for the page to be loaded
    And I log in as "Mr"
    Then I should see "Your password or username was incorrect."

  Scenario: When changing the username the max length must be 180
    Given I click "#top-app-bar__btn-settings"
    And I wait for the element "#user-settings-modal" to be visible
    And I click ".profile__user-settings .nav-link[data-bs-target='#profile-settings-modal']"
    And I wait for the element "#profile-settings-modal" to be visible
    When I fill in "username" with "ThisUsernameConsistOf185CharsThisUsernameConsistOfMoreThan180CharsThisUsernameConsistOfMoreThan180CharsThisUsernameConsistOfMoreThan180CharsThisUsernameConsistOfMoreThan180Chars!!!+++++"
    And I click "#profile_settings-save_action"
    And I wait for the page to be loaded
    Then I should see "Username too long"

  Scenario: changing email should work
    Given I click "#top-app-bar__btn-settings"
    And I wait for the element "#user-settings-modal" to be visible
    And I click ".profile__user-settings .nav-link[data-bs-target='#profile-settings-modal']"
    And I wait for the element "#profile-settings-modal" to be visible
    When I fill in "email" with "first@email.com"
    And I click "#profile_settings-save_action"
    And I wait for the page to be loaded
    Then I should be on "/app/user"
    And I should see "Your profile has been successfully changed."
    When I reload the page
    And I wait for the page to be loaded
    Then the "email" field should contain "first@email.com"

  Scenario: changing email addresses with an invalid email should not work
    Given I click "#top-app-bar__btn-settings"
    And I wait for the element "#user-settings-modal" to be visible
    And I click ".profile__user-settings .nav-link[data-bs-target='#profile-settings-modal']"
    And I wait for the element "#profile-settings-modal" to be visible
    When I fill in "email" with "first"
    And I click "#profile_settings-save_action"
    And I wait for the element ".swal2-shown" to be visible
    Then I should see "Email invalid"
    When I fill in "email" with "first@email"
    And I click "#profile_settings-save_action"
    And I wait for the element ".swal2-shown" to be visible
    Then I should see "Email invalid"

  Scenario: empty email not allowed
    Given I click "#top-app-bar__btn-settings"
    And I wait for the element "#user-settings-modal" to be visible
    And I click ".profile__user-settings .nav-link[data-bs-target='#profile-settings-modal']"
    And I wait for the element "#profile-settings-modal" to be visible
    When I fill in "email" with ""
    And I click "#profile_settings-save_action"
    Then the field "email" should not be valid

  Scenario: Change currently working on and about me
    Then the element ".profile__description__status__label" should not exist
    Given I click "#top-app-bar__btn-settings"
    And I wait for the element "#user-settings-modal" to be visible
    And I click ".profile__user-settings .nav-link[data-bs-target='#profile-settings-modal']"
    And I wait for the element "#profile-settings-modal" to be visible
    When I fill in "currentlyWorkingOn" with "an awesome project"
    When I fill in "about" with "I am a regular Catrobat user. Welcome on my profile."
    And I click "#profile_settings-save_action"
    Then I wait for the page to be loaded
    And I should see "Your profile has been successfully changed."
    And the element ".profile__description__status__label" should be visible
    And the ".profile__description__status__content" element should contain "an awesome project"
    And the ".profile__description__about" element should contain "I am a regular Catrobat user. Welcome on my profile."

  Scenario: Set and remove currently working on and about me again
    Given I click "#top-app-bar__btn-settings"
    And I wait for the element "#user-settings-modal" to be visible
    And I click ".profile__user-settings .nav-link[data-bs-target='#profile-settings-modal']"
    And I wait for the element "#profile-settings-modal" to be visible
    When I fill in "currentlyWorkingOn" with "an awesome project"
    When I fill in "about" with "I am a regular Catrobat user. Welcome on my profile."
    And I click "#profile_settings-save_action"
    Then I wait for the page to be loaded
    And the element ".profile__description__status__label" should be visible
    And the ".profile__description__status__content" element should contain "an awesome project"
    And the ".profile__description__about" element should contain "I am a regular Catrobat user. Welcome on my profile."
    Then I click "#top-app-bar__btn-settings"
    And I wait for the element "#user-settings-modal" to be visible
    And I click ".profile__user-settings .nav-link[data-bs-target='#profile-settings-modal']"
    And I wait for the element "#profile-settings-modal" to be visible
    When I fill in "currentlyWorkingOn" with ""
    When I fill in "about" with ""
    And I click "#profile_settings-save_action"
    Then I wait for the page to be loaded
    Then the element ".profile__description__status__label" should not exist
    And the element ".profile__description__status__content" should not exist
    And the element ".profile__description__about" should not exist
    And the element ".profile__description" should not exist

  Scenario: changing password must work
    Given I click "#top-app-bar__btn-settings"
    And I wait for the element "#user-settings-modal" to be visible
    And I click ".profile__user-settings .nav-link[data-bs-target='#security-settings-modal']"
    And I wait for the element "#security-settings-modal" to be visible
    When I fill in "current-password" with "123456"
    And I fill in "password" with "abcdef"
    And I fill in "repeat-password" with "abcdef"
    And I click "#security_settings-save_action"
    Then I wait for AJAX to finish
    And I wait for the element ".swal2-shown" to be visible
    Then I should see "Your password was successfully updated."
    When I reload the page
    Then I should be on "/app/user"
    And I should be logged in
    And I should see "My Profile"
    When I logout
    And I wait for the page to be loaded
    Then I should be logged out
    And I log in as "Catrobat"
    Then I should see "Your password or username was incorrect."
    When I log in as "Catrobat" with the password "abcdef"
    Then I should be logged in

  Scenario: changing password with a typo in repeat-password should not work
    Given I click "#top-app-bar__btn-settings"
    And I wait for the element "#user-settings-modal" to be visible
    And I click ".profile__user-settings .nav-link[data-bs-target='#security-settings-modal']"
    And I wait for the element "#security-settings-modal" to be visible
    When I fill in "current-password" with "123456"
    And I fill in "password" with "abcdef"
    And I fill in "repeat-password" with "fedcba"
    And I click "#security_settings-save_action"
    And I wait for the element ".swal2-shown" to be visible
    Then I should see "The passwords didn't match."

  Scenario: a short password should not work
    Given I click "#top-app-bar__btn-settings"
    And I wait for the element "#user-settings-modal" to be visible
    And I click ".profile__user-settings .nav-link[data-bs-target='#security-settings-modal']"
    And I wait for the element "#security-settings-modal" to be visible
    When I fill in "current-password" with "123456"
    And I fill in "password" with "abc"
    And I fill in "repeat-password" with "abc"
    And I click "#security_settings-save_action"
    And I wait for AJAX to finish
    And I wait for the element ".swal2-shown" to be visible
    Then I should see "Password too short"

  Scenario: too long password should not work
    Given I click "#top-app-bar__btn-settings"
    And I wait for the element "#user-settings-modal" to be visible
    And I click ".profile__user-settings .nav-link[data-bs-target='#security-settings-modal']"
    And I wait for the element "#security-settings-modal" to be visible
    When I fill in "current-password" with "123456"
    And I fill in "password" with "ThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLong!!!!!"
    And I fill in "repeat-password" with "ThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLong!!!!!"
    And I click "#security_settings-save_action"
    And I wait for AJAX to finish
    And I wait for the element ".swal2-shown" to be visible
    Then I should see "Password too long"

  Scenario: check project deletion PopUp
    Then I should see "project 1"
    And I should see "project 2"
    When I click ".own-project-list__project[data-id='1'] .own-project-list__project__action"
    Then I should see "Delete project"
    And the element "#project-action-menu" should be visible
    When I click "#project-action-menu > ul > li:nth-child(2)"
    Then I should see "delete it"
    When I click ".swal2-cancel"
    Then I should not see "delete it"
    When I reload the page
    Then I should see "project 1"
    And I should see "project 2"

  Scenario: Delete a project
    Then I should see "project 1"
    And I should see "project 2"
    When I click ".own-project-list__project[data-id='1'] .own-project-list__project__action"
    Then I should see "Delete project"
    And the element "#project-action-menu" should be visible
    When I click "#project-action-menu > ul > li:nth-child(2)"
    Then I should see "delete it"
    When I click ".swal2-confirm"
    And I wait for the page to be loaded
    And I wait for AJAX to finish
    Then I should not see "project 1"
    But I should see "project 2"

  Scenario: It should be possible toggle the project privacy on my profile
    Then I should see "project 1"
    And the ".own-project-list__project[data-id='1'] .own-project-list__project__details__visibility__text" element should contain "public"
    When I click ".own-project-list__project[data-id='1'] .own-project-list__project__action"
    Then the element "#project-action-menu" should be visible
    And I should see "Set private"
    When I click "#project-action-menu > ul > li:nth-child(1)"
    And I wait for the element ".swal2-shown" to be visible
    And I click ".swal2-confirm"
    And I wait for the element ".own-project-list__project[data-id='1'] .loading-spinner-backdrop" to appear and if so to disappear again
    Then the ".own-project-list__project[data-id='1'] .own-project-list__project__details__visibility__text" element should contain "private"
    When I click ".own-project-list__project[data-id='1'] .own-project-list__project__action"
    Then the element "#project-action-menu" should be visible
    And I should see "Set public"
    When I click "#project-action-menu > ul > li:nth-child(1)"
    And I wait for the element ".swal2-shown" to be visible
    And I click ".swal2-confirm"
    And I wait for the element ".own-project-list__project[data-id='1'] .loading-spinner-backdrop" to appear and if so to disappear again
    Then the ".own-project-list__project[data-id='1'] .own-project-list__project__details__visibility__text" element should contain "public"

  Scenario: Project visibility should not get updated if the user clicks on cancel
    Then I should see "project 1"
    And the ".own-project-list__project[data-id='1'] .own-project-list__project__details__visibility__text" element should contain "public"
    When I click ".own-project-list__project[data-id='1'] .own-project-list__project__action"
    Then the element "#project-action-menu" should be visible
    And I should see "Set private"
    When I click "#project-action-menu > ul > li:nth-child(1)"
    Then the element ".swal2-shown" should be visible
    When I click ".swal2-cancel"
    And I wait for AJAX to finish
    Then the ".own-project-list__project[data-id='1'] .own-project-list__project__details__visibility__text" element should contain "public"

  Scenario: It should be possible toggle the project privacy on my profile for more than 1 project
    Then I should see "project 1"
    And the ".own-project-list__project[data-id='1'] .own-project-list__project__details__visibility__text" element should contain "public"
    And I should see "project 2"
    And the ".own-project-list__project[data-id='2'] .own-project-list__project__details__visibility__text" element should contain "private"
    When I click ".own-project-list__project[data-id='1'] .own-project-list__project__action"
    Then the element "#project-action-menu" should be visible
    And I should see "Set private"
    When I click "#project-action-menu > ul > li:nth-child(1)"
    And I wait for the element ".swal2-shown" to be visible
    And I click ".swal2-confirm"
    And I wait for the element ".own-project-list__project[data-id='1'] .loading-spinner-backdrop" to appear and if so to disappear again
    And the ".own-project-list__project[data-id='1'] .own-project-list__project__details__visibility__text" element should contain "private"
    When I click ".own-project-list__project[data-id='2'] .own-project-list__project__action"
    Then the element "#project-action-menu" should be visible
    And I should see "Set public"
    When I click "#project-action-menu > ul > li:nth-child(1)"
    And I wait for the element ".swal2-shown" to be visible
    And I click ".swal2-confirm"
    And I wait for the element ".own-project-list__project[data-id='2'] .loading-spinner-backdrop" to appear and if so to disappear again
    Then the ".own-project-list__project[data-id='2'] .own-project-list__project__details__visibility__text" element should contain "public"

  Scenario: Projects with too high language version can also be set to visible
    Then I should see "project 2"
    And the ".own-project-list__project[data-id='2'] .own-project-list__project__details__visibility__text" element should contain "private"
    When I click ".own-project-list__project[data-id='2'] .own-project-list__project__action"
    Then the element "#project-action-menu" should be visible
    And I should see "Set public"
    When I click "#project-action-menu > ul > li:nth-child(1)"
    And I wait for the element ".swal2-shown" to be visible
    And I click ".swal2-confirm"
    And I wait for the element ".own-project-list__project[data-id='2'] .loading-spinner-backdrop" to appear and if so to disappear again
    Then the ".own-project-list__project[data-id='2'] .own-project-list__project__details__visibility__text" element should contain "public"

  Scenario: I should be able to delete my account
    Given I click "#top-app-bar__btn-settings"
    And I wait for the element "#user-settings-modal" to be visible
    Then I should see "User Settings"
    And I click ".profile__user-settings .nav-link[data-bs-target='#account-settings-modal']"
    And I wait for the element "#account-settings-modal" to be visible
    Then I should see "Account Settings"
    And I should see "You created 2 project(s) and have 0 follower(s). All of your projects will be removed."
    And the element "#btn-delete-account" should be visible
    When I click "#btn-delete-account"
    And I wait for the element ".swal2-shown" to be visible
    Then I should see "Account Deletion"
    When I click ".swal2-confirm"
    And I wait for the page to be loaded
    Then I should be on "/app/"
    And I should be logged out

  Scenario: I should be able to delete my account with comments and notifications
    Given I log in as "Catrobat" with the password "123456"
    And I am on "/app/user"
    And I wait for the page to be loaded
    And there are comments:
      | id | project_id | user_id | text |
      | 1  | 1          | 1       | c1   |
      | 2  | 2          | 2       | c2   |
      | 3  | 3          | 1       | c1   |
    And there are catro notifications:
      | user     | title | message | type           | commentID | like_from | follower_id | project_id | parent_project | child_project |
      | Catrobat |       |         | comment        | 2         |           |             |            |                |               |
      | Catrobat |       |         | like           |           | 2         |             | 2          |                |               |
      | Catrobat |       |         | follower       |           |           | 2           |            |                |               |
      | Catrobat | title | msg     | default        |           |           |             |            |                |               |
      | Catrobat |       |         | follow_project |           |           |             | 2          |                |               |
      | Catrobat |       |         | remix          |           |           |             |            | 1              | 3             |
      | User1    |       |         | comment        | 1         |           |             |            |                |               |
      | User1    |       |         | like           |           | 1         |             | 2          |                |               |
      | User1    |       |         | follower       |           |           | 1           |            |                |               |
      | User1    | title | msg     | default        |           |           |             |            |                |               |
      | User1    |       |         | follow_project |           |           |             | 2          |                |               |
      | User1    |       |         | remix          |           |           |             |            | 3              | 2             |
      | Catrobat | title | msg     | broadcast      |           |           |             |            |                |               |
      | User1    | title | msg     | broadcast      |           |           |             |            |                |               |
    And I click "#top-app-bar__btn-settings"
    And I wait for the element "#user-settings-modal" to be visible
    And I click ".profile__user-settings .nav-link[data-bs-target='#account-settings-modal']"
    And I wait for the element "#account-settings-modal" to be visible
    Then I should see "You created 2 project(s) and have 0 follower(s). All of your projects will be removed."
    When I click "#btn-delete-account"
    And I wait for the element ".swal2-shown" to be visible
    Then I should see "Account Deletion"
    When I click ".swal2-confirm"
    And I wait for the page to be loaded
    Then I should be logged out
    And the user "Catrobat" should not exist
    When I log in as "User1" with the password "123456"
    And I am on "/app/user"
    And I wait for the page to be loaded
    And I click "#top-app-bar__btn-settings"
    And I wait for the element "#user-settings-modal" to be visible
    And I click ".profile__user-settings .nav-link[data-bs-target='#account-settings-modal']"
    And I wait for the element "#account-settings-modal" to be visible
    Then I should see "You created 1 project(s) and have 0 follower(s). All of your projects will be removed."
    When I click "#btn-delete-account"
    And I wait for the element ".swal2-shown" to be visible
    Then I should see "Account Deletion"
    When I click ".swal2-confirm"
    And I wait for AJAX to finish
    Then I should be logged out
    And the user "User1" should not exist
    And comments or catro notifications should not exist

  Scenario: When changing the username it shouldn't contain an email address
    Given I click "#top-app-bar__btn-settings"
    And I wait for the element "#user-settings-modal" to be visible
    And I click ".profile__user-settings .nav-link[data-bs-target='#profile-settings-modal']"
    And I wait for the element "#profile-settings-modal" to be visible
    When I fill in "username" with "catro catro@gmail.com"
    And I click "#profile_settings-save_action"
    And I wait for the element ".swal2-shown" to be visible
    And I wait for AJAX to finish
    Then I should see "Username must not contain an email address"
    When I go to "/app/logout"
    And I wait for the page to be loaded
    And I log in as "Mr"
    Then I should see "Your password or username was incorrect."
