@web @notifications
Feature: User get notifications about achievements

  Background:
    Given there are users:
      | id | name     |
      | 1  | Achiever |
    And there are catro notifications:
      | id | user     | message                                                        | type            |
      | 1  | Achiever | Achievement - Congratulations, you uploaded your first app     | achievement     |
      | 2  | Achiever | Achievement - Congratulations, you reached a total of 2 views  | achievement     |
      | 3  | Achiever | Anniversary - 10 years Catrobat! Wuhuu                         | anniversary     |

  Scenario: User get notified about achievements and anniversaries
    Given I log in as "Achiever"
    And I am on "/app/user_notifications"
    And I wait for the page to be loaded
    Then I should see "Achievement - Congratulations, you uploaded your first app"
    And I should see "Achievement - Congratulations, you reached a total of 2 views"
    And I should see "Anniversary - 10 years Catrobat! Wuhuu"
