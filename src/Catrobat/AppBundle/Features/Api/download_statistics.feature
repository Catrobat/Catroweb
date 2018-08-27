@api
Feature: Downloaded program statistics

  Background: 
    Given there are users:
      | name     | password | token      |
      | Catrobat | 12345    | cccccccccc |
    And there are downloadable programs:
      | id | name      | description | owned by | downloads | views | upload time      | version | visible |
      | 1  | program 1 | p1          | Catrobat | 3         | 12    | 01.01.2013 12:00 | 0.8.5   | true    |

  @RealGeocoder
  Scenario: Download statistics should be persisted to database after successful download of a program
    When I have downloaded a valid program
    Then the program download statistic should have a download timestamp, an anonimous user and the following statistics:
      | ip              | country_code | country_name | program_id |
      | 127.0.0.1       | AT           | AUSTRIA      | 1          |
#    new geocode bundle work with fake ip (88.116.169.222) in query but we only save the request->getClientIp
#    so the ip will be 127.0.0.1
