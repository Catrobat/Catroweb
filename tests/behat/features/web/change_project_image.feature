@homepage
Feature:
  A user should be able to change his projects screenshot/thumbnail

  Background:
    Given there are users:
      | name     | password | token      | email        | id |
      | Catrobat | 123456   | cccccccccc | dev1@app.org |  1 |
      | User1    | 654321   | cccccccccc | dev2@app.org |  2 |
    And there are programs:
      | id | name       | description | owned by |
      | 1  | program 1  | p1          | Catrobat |
      | 2  | program 2  |             | Catrobat |

  Scenario: uploading new image should not work when not logged in
    And I am on "/app/project/1"
    And I should see "program 1"
    Then the element "#change-project-thumbnail-button" should not exist

  Scenario: uploading new a project image should not work when it is not my project
    Given I log in as "User1" with the password "654321"
    And I am on "/app/project/1"
    And I should see "program 1"
    Then the element "#change-project-thumbnail-button" should not exist

  Scenario: uploading new image should work when I am logged in and it is my program
    Given I log in as "Catrobat" with the password "123456"
    And I am on "/app/project/1"
    And I should see "program 1"
    When I click "#change-project-thumbnail-button"
    And I wait for the server response
    When I attach the avatar "logo.png" to "file"
    And I wait 250 milliseconds
    Then I should see "Your image was uploaded successfully!"
    And I click "#change-project-thumbnail-button"
    And I wait for the server response
    When I attach the avatar "galaxy.jpg" to "file"
    And I wait 250 milliseconds
    Then I should see "Your image was uploaded successfully!"
