@api @projects
Feature: Project files use the existing preview and archive access policies

  Background:
    Given there are users:
      | id | name       | is_minor | consent_status | parent_email   | role       | admin |
      | 1  | Owner      | false    | not_required   |                | ROLE_USER  | false |
      | 2  | Minor      | true     | granted        | parent@test.at | ROLE_USER  | false |
      | 3  | MinorOwner | true     | granted        | parent@test.at | ROLE_USER  | false |
      | 4  | Moderator  | false    | not_required   |                | ROLE_ADMIN | false |
      | 5  | SuperAdmin | false    | not_required   |                | ROLE_ADMIN | true  |
    And there are downloadable projects:
      | id | name       | owned by   | visible | auto_hidden | private | not_for_kids | downloads |
      | 1  | Safe       | Owner      | true    | false       | false   | 0            | 7         |
      | 2  | Adult      | Owner      | true    | false       | false   | 1            | 7         |
      | 3  | MinorOwned | MinorOwner | true    | false       | false   | 1            | 7         |
      | 4  | Unlisted   | Owner      | true    | false       | true    | 0            | 7         |
      | 5  | Hidden     | Owner      | false   | false       | false   | 0            | 7         |
      | 6  | AutoHidden | Owner      | true    | true        | false   | 0            | 7         |
      | 7  | Featured   | Owner      | false   | false       | false   | 0            | 7         |
    And following projects are featured:
      | project_id | active |
      | 7          | 1      |
    And project "1" has extracted code xml:
      """
      <?xml version="1.0"?><program><header/></program>
      """
    And project "2" has extracted code xml:
      """
      <?xml version="1.0"?><program><header/></program>
      """
    And project "3" has extracted code xml:
      """
      <?xml version="1.0"?><program><header/></program>
      """
    And project "4" has extracted code xml:
      """
      <?xml version="1.0"?><program><header/></program>
      """
    And project "5" has extracted code xml:
      """
      <?xml version="1.0"?><program><header/></program>
      """
    And project "6" has extracted code xml:
      """
      <?xml version="1.0"?><program><header/></program>
      """
    And project "7" has extracted code xml:
      """
      <?xml version="1.0"?><program><header/></program>
      """

  Scenario Outline: Guest preview and archive access retain their existing differences
    When I request "GET" "/project-files/<id>/assets/code.xml"
    Then the response status code should be "<preview>"
    When I request "GET" "/project-files/<id>/archive"
    Then the response status code should be "<archive>"

    Examples:
      | id | preview | archive |
      | 1  | 200     | 200     |
      | 2  | 200     | 200     |
      | 4  | 200     | 200     |
      | 5  | 404     | 404     |
      | 6  | 404     | 404     |
      | 7  | 200     | 404     |

  Scenario Outline: Owners and admins keep preview exceptions without broadening archive access
    Given I use a valid JWT Bearer token for "<viewer>"
    When I request "GET" "/project-files/<id>/assets/code.xml"
    Then the response status code should be "<preview>"
    When I request "GET" "/project-files/<id>/archive"
    Then the response status code should be "404"

    Examples:
      | viewer     | id | preview |
      | Owner      | 5  | 404     |
      | Owner      | 6  | 200     |
      | Moderator  | 5  | 404     |
      | Moderator  | 6  | 200     |
      | SuperAdmin | 5  | 200     |
      | SuperAdmin | 6  | 200     |
      | SuperAdmin | 7  | 200     |

  Scenario: Minors cannot fetch another user's not-for-kids files
    Given I use a valid JWT Bearer token for "Minor"
    When I request "GET" "/project-files/2/assets/code.xml"
    Then the response status code should be "404"
    When I request "GET" "/project-files/2/archive"
    Then the response status code should be "404"
    When I request "GET" "/project-files/1/assets/code.xml"
    Then the response status code should be "200"

  Scenario: Minor owners retain access to their own files
    Given I use a valid JWT Bearer token for "MinorOwner"
    When I request "GET" "/project-files/3/assets/code.xml"
    Then the response status code should be "200"
    When I request "GET" "/project-files/3/archive"
    Then the response status code should be "200"

  Scenario: Preview requests and archive HEAD requests do not count as downloads
    Given I use a valid JWT Bearer token for "Owner"
    When I request "GET" "/project-files/1/assets/code.xml"
    Then the response status code should be "200"
    And the project "1" should have "7" downloads
    When I request "HEAD" "/project-files/1/archive"
    Then the response status code should be "200"
    And the project "1" should have "7" downloads

  Scenario Outline: Legacy paths and direct internal locations are rejected by the application
    When I request "GET" "<url>"
    Then the response status code should be "404"

    Examples:
      | url                                                   |
      | /resources_test/projects/1.catrobat                    |
      | /resources_test/extract/1/code.xml                     |
      | /resources/assets/ab/cd/unknown                        |
      | /extract/1/code.xml                                    |
      | /__protected-project-resources/extract/1/code.xml       |

  Scenario Outline: Encoded separators and traversal are rejected
    When I request "GET" "<url>"
    Then the response status code should be "404"

    Examples:
      | url                                                    |
      | /project-files/1/assets/images%2flook.png                |
      | /project-files/1/assets/images%252flook.png              |
      | /project-files/1/assets/images/%2e%2e/code.xml            |
      | /project-files/1/assets/images%5clook.png                |
