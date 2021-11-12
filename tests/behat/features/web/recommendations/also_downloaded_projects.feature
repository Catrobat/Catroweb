@web @recommendations @disabled
Feature: Users see the recommended projects that have been downloaded by other users on project page

  Background:
    Given there are users:
      | id | name      |
      | 1  | Catrobat  |
      | 2  | OtherUser |
    And there are projects:
      | id | name    | owned by  |
      | 1  | Minions | Catrobat  |
      | 2  | Galaxy  | OtherUser |
      | 3  | Alone   | Catrobat  |
    And there are project reactions:
      | user      | project | type | created at       |
      | Catrobat  | 1       | 1    | 01.01.2017 12:00 |
      | Catrobat  | 2       | 2    | 01.01.2017 12:00 |
      | OtherUser | 1       | 4    | 01.01.2017 12:00 |

  Scenario: Users see the recommended projects that have been downloaded by other users on project page
    Given there are program download statistics:
      | id | program_id | downloaded_at       | ip             | country_code | country_name | user_agent | username  | referrer |
      | 1  | 1          | 2017-02-09 16:01:00 | 88.116.169.222 | AT           | Austria      | okhttp     | OtherUser | Facebook |
      | 2  | 3          | 2017-02-09 16:02:00 | 88.116.169.222 | AT           | Austria      | okhttp     | OtherUser | Facebook |
    And I am on "/app/project/1"
    And I wait for the page to be loaded
    Then the element "#recommended-projects__also_downloaded" should be visible
    And I should see 1 "#recommended-projects__also_downloaded .project-list__project"
    And I should see "Alone"
    And I should not see "Galaxy"

  Scenario: No other recommended projects that have been downloaded are available
    Given there are program download statistics:
      | id | program_id | downloaded_at       | ip             | country_code | country_name | user_agent | username  | referrer |
      | 1  | 1          | 2017-02-09 16:01:00 | 88.116.169.222 | AT           | Austria      | okhttp     | OtherUser | Facebook |
      | 2  | 3          | 2017-02-09 16:02:00 | 88.116.169.222 | AT           | Austria      | okhttp     | OtherUser | Facebook |
    And I am on "/app/project/2"
    And I wait for the page to be loaded
    Then the element "#recommended-projects__also_downloaded" should not be visible
    And I should see "Galaxy"
    And I should not see "Minions"
    And I should not see "Alone"
