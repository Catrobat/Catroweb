@web @project_page
Feature: As a project owner, I should be able to give credits for my project.

  Background:
    Given there are users:
      | id | name      |
      | 1  | Catrobat  |
      | 2  | OtherUser |
    And there are projects:
      | id | name      | owned by  | approved |
      | 1  | project 1 | Catrobat  |  false   |
      | 2  | project 2 | OtherUser |  false   |
      | 3  | project 3 | Catrobat  |  false   |
      | 4  | project 4 | Catrobat  |  true    |

    And following projects are featured:
      | name      | active | priority |
      | project 1 | 1      | 1        |

  Scenario: I should not see the report button for my own projects
    Given I log in as "Catrobat"
    When I am on "/app/project/1"
    And I wait for the page to be loaded
    And I click "#top-app-bar__btn-options"
    Then the element "#top-app-bar__btn-report-project" should not exist

  Scenario: report project when not logged in should forward to login page, than back to project page
    Given I am on "/app/project/1"
    And I wait for the page to be loaded
    When I click "#top-app-bar__btn-report-project"
    And I wait for AJAX to finish
    Then I should be on "/app/login"
    When I fill in "username" with "OtherUser"
    And I fill in "password" with "123456"
    And I press "Login"
    And I wait for the page to be loaded
    Then I should be on "/app/project/1#login"

  Scenario: The report pop up should have a session where reason and checked category is stored
    Given I log in as "OtherUser"
    And I am on "/app/project/1"
    And I wait for the page to be loaded
    When I click "#top-app-bar__btn-report-project"
    And I wait for AJAX to finish
    Then I should see "Why do you want to report this project?"
    When I fill in "report-reason" with "Super secret message"
    And I click the "#report-copyright" RadioButton
    And I wait for AJAX to finish
    And I click ".swal2-cancel"
    And I wait for AJAX to finish
    When I click "#top-app-bar__btn-report-project"
    And I wait for AJAX to finish
    Then the "report-reason" field should contain "Super secret message"
    And the "report-copyright" checkbox should be checked
    When I fill in "report-reason" with "Magic"
    And I click ".swal2-cancel"
    And I go to "/app/project/3"
    And I wait for the page to be loaded
    And I click "#top-app-bar__btn-report-project"
    And I wait for AJAX to finish
    Then the "report-reason" field should not contain "Super secret message"
    And the "report-reason" field should not contain "Magic"
    And the "report-copyright" checkbox should not be checked
    When I am on "/app/project/1"
    And I wait for the page to be loaded
    When I click "#top-app-bar__btn-report-project"
    And I wait for AJAX to finish
    Then the "report-reason" field should not contain "Super secret message"
    Then the "report-reason" field should contain "Magic"
    And the "report-copyright" checkbox should be checked

  Scenario: I should be able to report a project as inappropriate
    Given I log in as "OtherUser"
    And I am on "/app/project/1"
    And I wait for the page to be loaded
    When I click "#top-app-bar__btn-report-project"
    And I wait for AJAX to finish
    Then I should see "Why do you want to report this project?"
    And I click the "#report-inappropriate" RadioButton
    And I wait for AJAX to finish
    And I fill in "report-reason" with "I do not like this project ... hehe"
    And I click ".swal2-confirm"
    And I wait for AJAX to finish
    Then I should see "Your report was successfully sent!"
    When I click ".swal2-confirm"
    And I wait for the page to be loaded
    Then I should be on "/app/"

  Scenario: I should be able to report a project due to copyright infringement
    Given I log in as "OtherUser"
    And I am on "/app/project/1"
    And I wait for the page to be loaded
    When I click "#top-app-bar__btn-report-project"
    And I wait for AJAX to finish
    Then I should see "Why do you want to report this project?"
    And I click the "#report-copyright" RadioButton
    And I fill in "report-reason" with "That was my idea!!!"
    And I click ".swal2-confirm"
    And I wait for AJAX to finish
    Then I should see "Your report was successfully sent!"
    When I click ".swal2-confirm"
    And I wait for the page to be loaded
    Then I should be on "/app/"

  Scenario: I should be able to report a project due to spam
    Given I log in as "OtherUser"
    And I am on "/app/project/1"
    And I wait for the page to be loaded
    When I click "#top-app-bar__btn-report-project"
    And I wait for AJAX to finish
    Then I should see "Why do you want to report this project?"
    And I click the "#report-spam" RadioButton
    And I fill in "report-reason" with "That's just spam!!!"
    And I click ".swal2-confirm"
    And I wait for AJAX to finish
    Then I should see "Your report was successfully sent!"
    When I click ".swal2-confirm"
    And I wait for the page to be loaded
    Then I should be on "/app/"

  Scenario: I should be able to report a project due to dislike
    Given I log in as "OtherUser"
    And I am on "/app/project/1"
    And I wait for the page to be loaded
    When I click "#top-app-bar__btn-report-project"
    And I wait for AJAX to finish
    Then I should see "Why do you want to report this project?"
    And I click the "#report-dislike" RadioButton
    And I wait for AJAX to finish
    And I fill in "report-reason" with "I do not like this project ... hehe"
    And I click ".swal2-confirm"
    And I wait for AJAX to finish
    Then I should see "Your report was successfully sent!"
    When I click ".swal2-confirm"
    And I wait for the page to be loaded
    Then I should be on "/app/"

  Scenario: A reported project should not be visible anymore
    Given I log in as "OtherUser"
    And I am on "/app/project/3"
    And I wait for the page to be loaded
    When I click "#top-app-bar__btn-report-project"
    And I wait for AJAX to finish
    Then I should see "Why do you want to report this project?"
    And I click the "#report-spam" RadioButton
    And I fill in "report-reason" with "That's just spam!!!"
    And I click ".swal2-confirm"
    And I wait for AJAX to finish
    Then I should see "Your report was successfully sent!"
    When I click ".swal2-confirm"
    And I wait for the page to be loaded
    Then I should be on "/app/"
    When I go to "/app/project/3"
    And I wait for the page to be loaded
    Then I should see "Ooooops something went wrong."

  Scenario: Clicking on a reported featured project should still show its page
    Given I log in as "OtherUser"
    And I am on "/app/project/1"
    And I wait for the page to be loaded
    When I click "#top-app-bar__btn-report-project"
    And I wait for AJAX to finish
    Then I should see "Why do you want to report this project?"
    And I click the "#report-spam" RadioButton
    And I fill in "report-reason" with "That's just spam!!!"
    And I click ".swal2-confirm"
    And I wait for AJAX to finish
    Then I should see "Your report was successfully sent!"
    When I click ".swal2-confirm"
    And I wait for the page to be loaded
    Then I should be on "/app/"
    When I go to "/app/project/1"
    And I wait for the page to be loaded
    Then I should see "project 1"

  Scenario: Clicking on a approved project should still show its page
    Given I log in as "OtherUser"
    And I am on "/app/project/4"
    And I wait for the page to be loaded
    When I click "#top-app-bar__btn-report-project"
    And I wait for AJAX to finish
    Then I should see "Why do you want to report this project?"
    And I click the "#report-spam" RadioButton
    And I fill in "report-reason" with "That's just spam!!!"
    And I click ".swal2-confirm"
    And I wait for AJAX to finish
    Then I should see "Your report was successfully sent!"
    When I click ".swal2-confirm"
    And I wait for the page to be loaded
    Then I should be on "/app/"
    When I go to "/app/project/4"
    And I wait for the page to be loaded
    Then I should see "project 4"
