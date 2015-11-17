@api
Feature: Downloaded program statistics

  Background: 
    Given there are users:
      | name     | password | token      |
      | Catrobat | 12345    | cccccccccc |
    And there are downloadable programs:
      | id | name      | description | owned by | downloads | views | upload time      | version | visible |
      | 1  | program 1 | p1          | Catrobat | 3         | 12    | 01.01.2013 12:00 | 0.8.5   | true    |


  Scenario: Download statistics should be persisted to database after successful download of a program
    Given I use the real DownloadStatisticsService
    When I have downloaded a valid program
    And I really generate the download statistics
    Then the program should have a download timestamp, street, postal code, locality, latitude of approximately "47", longitude of approximately "11" and the following statistics:
      | ip              | country_code | country_name | program_id |
      | 88.116.169.222  | AT           | AUSTRIA      | 1          |
