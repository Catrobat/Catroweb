@admin
Feature: Program Download Statistics
  In order to get information about program downloads
  As Admin
  I want to be able to see the statistics

Scenario: As a valid admin i want to be able to see the statistics
  Given I am a logged in as super admin
  And there are programs:
    | id | name      |  apk_status  | directory_hash  |
    | 1  | program 1 |  2           | null            |
    | 2  | program 2 |  2           | null            |
  And there are users:
    | name    | email        |  token  | password   |
    | cat     | cat@robat.at | ccccc   | highsecure |
  And there are program download statistics:
    | id | program_id | downloaded_at        | ip             | latitude      |  longitude  | country_code  | country_name | street              | postal_code      |  locality   | user_agent | user_name | referrer |
    | 1  | 1          |  2015-11-21 13:39:00 | 88.116.169.222 | 47.2          | 10.7        | AT            | Austria      | Duck Street 1       | 1234             | Entenhausen | okhttp     | cat       | Facebook |
  When I GET "/admin/download_stats/list"
  Then the response should contain the elements:
    | id | downloaded_at            | ip             | latitude      |  longitude  | country_code  | country_name | street              | postal_code      |  locality   | user_agent |user | referrer |
    | id | downloaded_at            | ip             | latitude      |  longitude  | country_code  | country_name | street              | postal_code      |  locality   | user_agent |user | referrer |
    | 1  | November 21, 2015 13:39  | 88.116.169.222 | 47.2          | 10.7        | AT            | Austria      | Duck Street 1       | 1234             | Entenhausen | okhttp     |cat  | Facebook |
