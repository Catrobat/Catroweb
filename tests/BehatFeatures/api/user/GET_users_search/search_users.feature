@api @user
Feature: Search users

  To find users, users should be able to search all available users for specific words

  Background:
    Given there are users:
      | name         | password | token      | id |
      | Catrobat     | 12345    | cccccccccc | 1  |
      | User1        | vwxyz    | aaaaaaaaaa | 2  |
      | NewUser      | 54321    | bbbbbbbbbb | 3  |
      | Catroweb     | 54321    | bbbbbbbbbb | 4  |
      | пользователь | 54321    | bbbbbbbbbb | 5  |
    And there are followers:
      | name     | following       |
      | Catrobat | User1, Catroweb |
      | NewUser  | Catrobat        |
      | Catroweb | User1, NewUser  |
    And there are projects:
      | id        | name     | owned by | version | private | visible |
      | isxs-adkt | Webteam  | Catroweb | 0.8.5   | false   | true    |
      | tvut-irkw | Catroweb | NewUser  | 0.8.5   | false   | true    |
    And I wait 500 milliseconds

  Scenario: Search for users with specific word in username

    Given I have a parameter "query" with value "Catro"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/users/search"
    Then the response status code should be "200"
    Then the response should have the default users model structure
    Then the response should contain users in the following order:
      | Name     |
      | Catrobat |
      | Catroweb |

  Scenario: Search for users with specific word in username

    Given I have a parameter "query" with value "NewUser"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/users/search"
    Then the response status code should be "200"
    Then the response should have the default users model structure
    Then the response should contain users in the following order:
      | Name    |
      | NewUser |

  Scenario: Search for users with specific word in username

    Given I have a parameter "query" with value "п"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/users/search"
    Then the response status code should be "200"
    Then the response should have the default users model structure
    Then the response should contain users in the following order:
      | Name         |
      | пользователь |

  Scenario: Search for users with specific word in id

    Given I have a parameter "query" with value "2"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/users/search"
    Then the response status code should be "200"
    Then the response should have the default users model structure
    Then the response should contain users in the following order:
      | Name  |
      | User1 |

  Scenario: Search for users with specific word should only return users

    Given I have a parameter "query" with value "Catroweb"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/users/search"
    Then the response status code should be "200"
    Then the response should have the default users model structure
    Then the response should contain users in the following order:
      | Name     |
      | Catroweb |

  Scenario: Search for users with specific word should only return users; specified attributes

    Given I have a parameter "query" with value "Catroweb"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I have a parameter "attributes" with value "id,username,following"
    And I request "GET" "/api/users/search"
    Then the response status code should be "200"
    And I should get the json object:
    """
    [
      {
        "id": "4",
        "username": "Catroweb",
        "following": 2
      }
    ]
    """
