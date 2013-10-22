@api
Feature: Upload a project 

  Background: 
    Given there are users:
      | name     | password | token      |
      | Catrobat | 12345    | cccccccccc |
      | User1    | vwxyz    | aaaaaaaaaa |
    And there are projects:
      | id | name      | description | owned by | downloads | views | upload time      | version |
      | 1  | project 1 | p1          | Catrobat | 3         | 12    | 01.01.2013 12:00 | 0.8.5   |
      | 2  | project 2 |             | Catrobat | 33        | 9     | 01.02.2013 13:00 | 0.8.5   |
      | 3  | project 3 |             | User1    | 133       | 33    | 01.01.2012 13:00 | 0.8.5   |

  @TODO
  Scenario: project upload with valid data
    Given I have the username "Catrobat"
    And I have a token "cccccccccc"
    And I have a file "test.catrobat"
    And I have the md5sum of "test.catrobat"
    When I call "/api/upload/upload.json" with the given data
    Then I should see:
      """
      {"projectId":<id>,"statusCode":200,"answer":"Your project was uploaded successfully!","token":<token>,"preHeaderMessages":""}
      """
