@api
Feature: Getting data from the media lib api even though no data is present should not crash the server

  Scenario: get all files from media library with an empty library
    When I GET from the api "/app/api/media/json"
    Then I should get the json object:
    """
    [

    ]
    """

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
    Given there are mediapackages:
      | id | name  | name_url |
      | 1  | Looks | looks    |
    When I GET from the api "/app/api/media/package/Looks/json"
    Then I should get the json object:
    """
    [

    ]
    """

  Scenario: get all files from a media lib package by nameUrl when the package does exist but there are no categories
    Given there are mediapackages:
      | id | name  | name_url |
      | 1  | Looks | looks    |
    When I GET from the api "/app/api/media/packageByNameUrl/looks/json"
    Then I should get the json object:
    """
    [

    ]
    """

  Scenario: get all files from a media lib package and a certain category of that package but the package does not exist
    When I GET from the api "/app/api/media/package/Looks/Space/json"
    Then I should get the json object:
    """
    {
      "statusCode": 523,
      "message": "Looks not found"
    }
    """

  Scenario: get all files from a media lib package and a certain category when the package does exist but has no categories
    Given there are mediapackages:
      | id | name  | name_url |
      | 1  | Looks | looks    |
    When I GET from the api "/app/api/media/package/Looks/Space/json"
    Then I should get the json object:
    """
    {
      "statusCode": 522,
      "message": "category Space not found in package Looks because the package doesn't contain any categories"
    }
    """

  Scenario: get all files from a media lib package and a certain category when the package does exist but the wanted category does not
    Given there are mediapackages:
      | id | name  | name_url |
      | 1  | Looks | looks    |
    Given there are mediapackage categories:
      | id | name    | package |
      | 1  | Animals | Looks   |
    When I GET from the api "/app/api/media/package/Looks/Space/json"
    Then I should get the json object:
    """
    {
      "statusCode": 522,
      "message": "category Space not found in package Looks"
    }
    """

  Scenario: get all files from a media lib package and a certain category when the package and the category exist but there are no files
    Given there are mediapackages:
      | id | name  | name_url |
      | 1  | Looks | looks    |
    Given there are mediapackage categories:
      | id | name  | package |
      | 1  | Space | Looks   |
    When I GET from the api "/app/api/media/package/Looks/Space/json"
    Then I should get the json object:
    """
    [

    ]
    """

  Scenario: get all files from a media lib category when the category does not exist
    When I GET from the api "/app/api/media/category/Animals/json"
    Then I should get the json object:
    """
    {
      "statusCode": 522,
      "message": "category Animals not found"
    }
    """

  Scenario: get all files from a media lib category when the category exists but there are no files
    Given there are mediapackages:
      | id | name  | name_url |
      | 1  | Looks | looks    |
    Given there are mediapackage categories:
      | id | name  | package |
      | 1  | Space | Looks   |
    When I GET from the api "/app/api/media/category/Space/json"
    Then I should get the json object:
    """
    {
        "statusCode": 200,
        "data": []
    }
    """

  Scenario: get a single media file by id which does not exist
    When I GET from the api "/app/api/media/file/1/json"
    Then I should get the json object:
    """
    {
      "statusCode":404
    }
    """