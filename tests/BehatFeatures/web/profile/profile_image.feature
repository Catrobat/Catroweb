@web @profile_page
Feature:
  In order to manage the my profile page
  As a logged in user
  I want to be able to change my password, email, and upload an avatar image and delete my own projects

  Background:
    Given there are users:
      | id | name     |
      | 1  | Catrobat |
    And I log in as "Catrobat"
    And I am on "/app/user"
    And I wait for AJAX to finish
    Then I should see "My Profile"

  Scenario: uploading avatar should work
    When I attach the avatar "logo.png" to "own-profile-picture-upload-field"
    And I wait for the page to be loaded
    And I wait for the element "#alert-profile-picture-change-success" to be visible
    And I wait for the element "#alert-profile-picture-change-success" to contain "Your image was uploaded successfully!"
    Then the avatar img tag should not have the "default" data url

  Scenario: upload corrupt image as profile picture
    When I attach the avatar "corrupt.jpg" to "own-profile-picture-upload-field"
    And I wait for the page to be loaded
    Then I wait for the element ".swal2-modal" to be visible
    And I wait for the element ".swal2-html-container" to contain "Profile picture invalid or not supported"
    Then the avatar img tag should have the "default" data url
