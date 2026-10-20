@web @profile_page
Feature: Changing a password keeps the current browser signed in with rotated cookies

  Scenario: Security settings rotate the current session and revoke the other sessions
    Given there are users:
      | id | name     | password | verified |
      | 1  | Catrobat | Old12345 | true     |
    And user "Catrobat" has refresh sessions "other-browser-session"
    And I log in as "Catrobat" with the password "Old12345"
    And I am on "/app/user"
    And I wait for the page to be loaded
    And I remember my authentication cookies
    When I click "#top-app-bar__btn-settings"
    And I wait for the element "#user-settings-modal" to be visible
    And I click ".profile__user-settings .nav-link[data-bs-target='#security-settings-modal']"
    And I wait for the element "#security-settings-modal" to be visible
    And I fill in "profile-current-password" with "Old12345"
    And I fill in "profile-password" with "NewPassword123"
    And I fill in "profile-repeat-password" with "NewPassword123"
    And I click "#security_settings-save_action"
    And I wait for the element ".swal2-success" to be visible
    Then my authentication cookies should both be rotated
    And user "Catrobat" should have "1" refresh sessions
    And refresh session "other-browser-session" should be revoked
    When I am on "/app/user"
    And I wait for the page to be loaded
    Then I should be logged in
