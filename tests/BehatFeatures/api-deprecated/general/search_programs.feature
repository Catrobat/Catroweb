@api
Feature: Search programs

  To find programs, users should be able to search all available programs for specific words

  Background:
    Given there are users:
      | name     | password | id |
      | Catrobat | 12345    | 1  |
      | User1    | vwxyz    | 2  |
      | NewUser  | 54321    | 3  |
    And there are projects:
      | id        | name            | description               | owned by | upload time      | version |
      | qysm-rhwt | Galaxy War      | description1              | User1    | 01.01.2014 12:00 | 0.8.5   |
      | phci-etqx | Minions         |                           | Catrobat | 02.02.2014 14:00 | 0.8.5   |
      | bbns-hixd | Fisch           |                           | User1    | 10.01.2012 14:00 | 0.8.5   |
      | rppk-kkri | Ponny           | description2              | User1    | 09.01.2012 14:00 | 0.8.5   |
      | nhre-xzvg | MarkoTheBest    |                           | NewUser  | 08.01.2012 14:00 | 0.8.5   |
      | ydmf-tbms | Whack the Marko | Universe                  | Catrobat | 07.02.2012 14:00 | 0.8.5   |
      | anxu-nsss | Superponny      | description1 description2 | User1    | 06.01.2012 14:00 | 0.8.5   |
      | kbrw-khwf | Universe        |                           | NewUser  | 05.01.2012 14:00 | 0.8.5   |
      | isxs-adkt | Webteam         |                           | NewUser  | 04.01.2012 14:00 | 0.8.5   |
      | tvut-irkw | Fritz the Cat   |                           | NewUser  | 03.01.2012 14:00 | 0.8.5   |
    And the current time is "01.08.2014 14:00"
    And I wait 500 milliseconds


  Scenario: A request must have specific parameters to succeed

    Given I have a parameter "q" with value "Galaxy"
    And I have a parameter "limit" with value "1"
    And I have a parameter "offset" with value "0"
    When I GET "/app/api/projects/search.json" with these parameters
    Then I should get following projects:
      | name       |
      | Galaxy War |


  Scenario: The result is in JSON format

    When I search for "Galaxy"
    Then I should get the json object:
    """
    {
     "CatrobatProjects":[{
         "ProjectId": "REGEX_STRING_WILDCARD",
         "ProjectName":"Galaxy War",
         "ProjectNameShort":"Galaxy War",
         "Author":"User1",
         "Description":"description1",
         "Version":"0.8.5",
         "Views":0,
         "Downloads":0,
         "Private":false,
         "Uploaded":"REGEX_INT_WILDCARD",
         "UploadedString":"REGEX_STRING_WILDCARD",
         "ScreenshotBig":"images/default/screenshot.png",
         "ScreenshotSmall":"images/default/thumbnail.png",
         "ProjectUrl":"app/project/REGEX_STRING_WILDCARD",
         "DownloadUrl":"api/project/REGEX_STRING_WILDCARD/catrobat",
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
    Then I should get following projects:
      | name    |
      | Minions |


  Scenario: The results can be limited and offset

    Given I have a parameter "limit" with value "10"
    And I have a parameter "offset" with value "0"
    When I search for "marko"
    Then I should get following projects:
      | name            |
      | Whack the Marko |
      | MarkoTheBest    |

  Scenario: If nothing is found the following JSON is returned

    When I search for "NOTHINGTOBEFIOUND"
    Then I should get the json object:
    """
    {
      "CatrobatProjects":[],
      "completeTerm":"",
      "preHeaderMessages":"",
      "CatrobatInformation": {
        "BaseUrl":"http:\/\/localhost\/",
        "TotalProjects":0,
        "ProjectsExtension":".catrobat"
    }
    }
    """


  Scenario: search program by description

    Given I use the limit "10"
    When I search for "description1"
    Then I should get following projects:
      | name       |
      | Galaxy War |
      | Superponny |


  Scenario: search program by description

    Given I use the limit "10"
    When I search for "description2"
    Then I should get following projects:
      | name       |
      | Ponny      |
      | Superponny |


  Scenario: searching using the AND relation between the words should only show related programs

    Given I use the limit "10"
    When I search for "description1 description2"
    Then I should get following projects:
      | name       |
      | Superponny |

  Scenario: if the query matches in title and description, return the program with the matching title first

    Given I use the limit "10"
    When I search for "Universe"
    Then I should get following projects:
      | name            |
      | Universe        |
      | Whack the Marko |


  Scenario Outline: the result contains the number of all projects effected by the search, independent from offset and limit

    Given I use the limit "<Limit>"
    Given I use the offset "<Offset>"
    When I search for "<Search>"
    Then I should get a total of <TotalProjects> projects

    Examples:
      | Search | Limit | Offset | TotalProjects |
      | Marko  | 5     | 0      | 2             |

  Scenario: find a program with its id

    Given I use the limit "10"
    And I use the offset "0"
    When I search for "kbrw"
    Then I should get following projects:
      | name     |
      | Universe |

  Scenario: find a program with its id

    Given I use the limit "10"
    And I use the offset "0"
    When I search for "phci"
    Then I should get following projects:
      | name    |
      | Minions |

  Scenario: find a program with its id

    Given I use the limit "10"
    And I use the offset "0"
    When I search for "-etqx"
    Then I should get following projects:
      | name    |
      | Minions |

  Scenario: only show visible programs
    Given project "Ponny" is not visible
    And I wait 500 milliseconds
    And I use the limit "10"
    When I search for "description2"
    Then I should get following projects:
      | name       |
      | Superponny |
