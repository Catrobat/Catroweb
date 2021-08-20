@admin
Feature: Program Download Statistics
  In order to get information about program downloads
  As Admin
  I want to be able to see the statistics

  Scenario: As a valid admin i want to be able to see the statistics
    Given I am a logged in as super admin
    And there are programs:
      | id | name      | apk_status | directory_hash |
      | 1  | program 1 | ready      | null           |
      | 2  | program 2 | ready      | null           |
    And there are users:
      | name | email        | token | password   | id |
      | cat  | cat@robat.at | ccccc | highsecure | 2  |
    And there are program download statistics:
      | id | program_id | downloaded_at       | ip             | country_code | country_name | user_agent | user_name | referrer |
      | 1  | 1          | 2015-11-21 13:39:00 | 88.116.169.222 | AT           | Austria      | okhttp     | cat       | Facebook |
    When I GET "/admin/download_stats/list"
    Then the client response should contain the elements:
      | id | downloaded_at           | ip             | country_code | country_name | user_agent | user | referrer |
      | 1  | November 21, 2015 13:39 | 88.116.169.222 | AT           | Austria      | okhttp     | cat  | Facebook |
