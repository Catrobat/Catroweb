@api
Feature: Getting data from the media lib api even though no data is present should not crash the server

  Scenario: get all files from a media lib package when the package does not exist
    When I GET from the api "/app/api/media/package/Looks/json"
    Then I should get the json object:
    """
    {
      "statusCode": 523,
      "message": "Looks not found"
    }
    """

  Scenario: get all files from a media lib package byNameUrl when the package does not exist
    When I GET from the api "/app/api/media/packageByNameUrl/looks/json"
    Then I should get the json object:
    """
    {
      "statusCode": 523,
      "message": "looks not found"
    }
    """

  Scenario: get all files from a media lib package when the package does exist but there are no categories
    Given there are media packages:
      | id | name  | name_url |
      | 1  | Looks | looks    |
    When I GET from the api "/app/api/media/package/Looks/json"
    Then I should get the json object:
    """
    [

    ]
    """

  Scenario: get all files from a media lib package by nameUrl when the package does exist but there are no categories
    Given there are media packages:
      | id | name  | name_url |
      | 1  | Looks | looks    |
    When I GET from the api "/app/api/media/packageByNameUrl/looks/json"
    Then I should get the json object:
    """
    [

    ]
    """