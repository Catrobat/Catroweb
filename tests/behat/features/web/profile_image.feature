@web @profile_page
Feature:
  In order to manage the my profile page
  As a logged in user
  I want to be able to change my password, email and country, upload an avatar image and delete my own programs

  Background:
    Given there are users:
      | id | name     |
      | 1  | Catrobat |
    And I start a new session
    And I log in as "Catrobat"
    And I am on "/app/user"
    And I wait for the page to be loaded
    And I should see "My Profile"

  Scenario: uploading avatar should work
    When I click "#avatar-upload"
    And I wait for AJAX to finish
    When I attach the avatar "logo.png" to "file"
    And I wait for AJAX to finish
    And I wait for the element ".text-img-upload-success" to be visible
    And I wait for the element ".text-img-upload-success" to contain "Your image was uploaded successfully!"
    Then the avatar img tag should have the "logo.png" data url

  Scenario: only jpg, png or gif allowed for avatar
    When I click "#avatar-upload"
    And I wait for AJAX to finish
    And I attach the avatar "fail.tif" to "file"
    And I wait for AJAX to finish
    And I wait for the element ".text-mime-type-not-supported" to be visible
    And I wait for the element ".text-mime-type-not-supported" to contain "This image type is not supported, please try another image."
    Then the avatar img tag should not have the "fail.tif" data url

  Scenario: max. 5MB for avatar image
    When I click "#avatar-upload"
    And I attach the avatar "galaxy_big.png" to "file"
    Then I wait for the element ".text-img-upload-too-large" to be visible
    Then I wait for the element ".text-img-upload-too-large" to contain "Your chosen picture is too large, please do not use images larger than 5mb."