@api
Feature: Search programs

  To find programs, users should be able to search all available programs for specific words

  Background:
    Given there are users:
      | name     | password | token      |
      | Catrobat | 12345    | cccccccccc |
      | User1    | vwxyz    | aaaaaaaaaa |
      | NewUser  | 54321    | bbbbbbbbbb |
    And there are programs:
      | id | name            | description | owned by | downloads | views | upload time      | version |
      | 1  | Galaxy War      | p1          | User1    | 3         | 12    | 01.01.2013 12:00 | 0.8.5   |
      | 2  | Minions         |             | Catrobat | 33        | 9     | 01.02.2013 13:00 | 0.8.5   |
      | 3  | Fisch           |             | User1    | 133       | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 4  | Ponny           | p2          | User1    | 245       | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 5  | MarkoTheBest    |             | NewUser  | 335       | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 6  | Whack the Marko | Universe    | Catrobat | 2         | 33    | 01.02.2012 13:00 | 0.8.5   |
      | 7  | Superponny      | p1 p2 p3    | User1    | 4         | 33    | 01.01.2012 12:00 | 0.8.5   |
      | 8  | Universe        |             | User1    | 23        | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 9  | Webteam         |             | User1    | 100       | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 10 | Fritz the Cat   |             | User1    | 112       | 33    | 01.01.2012 13:00 | 0.8.5   |
    And the current time is "01.08.2014 13:00"


  Scenario: A request must have specific parameters to succeed

    Given I have a parameter "q" with value "Galaxy"
    And I have a parameter "limit" with value "1"
    And I have a parameter "offset" with value "0"
    When I GET "/app/api/projects/search.json" with these parameters
    Then I should get following programs:
      | Name       |
      | Galaxy War |


  Scenario: The result is in JSON format

    When I search for "Galaxy"
    Then I should get the json object:
    """
    {
     "CatrobatProjects":[{
         "ProjectId":1,
         "ProjectName":"Galaxy War",
         "ProjectNameShort":"Galaxy War",
         "Author":"User1",
         "Description":"p1",
         "Version":"0.8.5",
         "Views":"12",
         "Downloads":"3",
         "Private":false,
         "Uploaded":1357041600,
         "UploadedString":"more than one year ago",
         "ScreenshotBig":"images/default/screenshot.png",
         "ScreenshotSmall":"images/default/thumbnail.png",
         "ProjectUrl":"app/program/1",
         "DownloadUrl":"app/download/1.catrobat",
         "FileSize":0
     }],
     "completeTerm":"",
     "preHeaderMessages":"",
     "CatrobatInformation": {
         "BaseUrl":"http://localhost/",
         "TotalProjects":1,
         "ProjectsExtension":".catrobat"
     }
    }
    """


  Scenario: Search for a program with a certain name

    When I search for "Minions"
    Then I should get following programs:
      | Name    |
      | Minions |


  Scenario: The results can be limited and offset

    Given I have a parameter "limit" with value "10"
    And I have a parameter "offset" with value "0"
    When I search for "marko"
    Then I should get following programs:
      | Name            |
      | Whack the Marko |
      | MarkoTheBest    |


  Scenario: If a user is found with the given search query, return her programs

    When I search for "NewUser"
    Then I should get following programs:
      | Name         |
      | MarkoTheBest |


  Scenario: If nothing is found the following JSON is returned

    When I search for "NOTHINGTOBEFIOUND"
    Then I should get the json object:
    """
    {
      "completeTerm":"",
      "CatrobatInformation": {
        "BaseUrl":"http:\/\/localhost\/",
        "TotalProjects":0,
        "ProjectsExtension":".catrobat"
    },
    "CatrobatProjects":[],
    "preHeaderMessages":""
    }
    """


  Scenario: search program by description

    Given I use the limit "10"
    When I search for "p1"
    Then I should get following programs:
      | Name       |
      | Galaxy War |
      | Superponny |


  Scenario: search program by description

    Given I use the limit "10"
    When I search for "p2"
    Then I should get following programs:
      | Name       |
      | Ponny      |
      | Superponny |


  Scenario: if the query matches in title and description, return the program with the matching title first

    Given I use the limit "10"
    When I search for "Universe"
    Then I should get following programs:
      | Name            |
      | Universe        |
      | Whack the Marko |


  Scenario Outline: the result contains the number of all projects effected by the search, independent from offset and limit

    Given I use the limit "<Limit>"
    Given I use the offset "<Offset>"
    When I search for "<Search>"
    Then I should get a total of <TotalProjects> projects

    Examples:
      | Search | Limit | Offset | TotalProjects |
      | User1  | 1     | 1      | 7             |
      | User1  | 9     | 2      | 7             |
      | Marko  | 5     | 0      | 2             |


  Scenario: to browse programs in smaller chunks you can request a subset of found projects

    Given I use the limit "2"
    And I use the offset "0"
    When I search for "User1"
    Then I should get following programs:
      | Name       |
      | Galaxy War |
      | Fisch      |

  Scenario: to browse programs in smaller chunks you can request a subset of found projects

    Given I use the limit "1"
    And I use the offset "0"
    When I search for "User1"
    Then I should get following programs:
      | Name       |
      | Galaxy War |

  Scenario: to browse programs in smaller chunks you can request a subset of found projects

    Given I use the limit "1"
    And I use the offset "1"
    When I search for "User1"
    Then I should get following programs:
      | Name  |
      | Fisch |

  Scenario: find a program with its id

    Given I use the limit "10"
    And I use the offset "0"
    When I search for "8"
    Then I should get following programs:
      | Name     |
      | Universe |

  Scenario: find a program with its id

    Given I use the limit "10"
    And I use the offset "0"
    When I search for "2"
    Then I should get following programs:
      | Name       |
      | Minions    |
      | Ponny      |
      | Superponny |


  Scenario: only show visible programs
    Given program "Ponny" is not visible
    And I use the limit "10"
    When I search for "p2"
    Then I should get following programs:
      | Name       |
      | Superponny |
