@api @authentication
Feature: Oauth login should validate id tokens

  Background:
    Given there are users:
      | id | name     | password |
      | 1  | Catrobat | 123456   |

  Scenario: Invalid google id token should return an error
    Given I have the following JSON request body:
    """
      {
        "id_token": "ee.ee.ee",
        "resource_owner": "google"
      }
    """
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "POST" "/api/authentication/oauth"
    Then the response status code should be "401"
    And I should get the json object:
    """
     {
     }
    """
  Scenario: Invalid apple id token should return an error
    Given I have the following JSON request body:
    """
      {
        "id_token": "ee.ee.ee",
        "resource_owner": "apple"
      }
    """
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "POST" "/api/authentication/oauth"
    Then the response status code should be "401"
    And I should get the json object:
    """
     {
     }
    """
  Scenario: Invalid facebook id token should return an error
    Given I have the following JSON request body:
    """
      {
        "id_token": "ee.ee.ee",
        "resource_owner": "facebook"
      }
    """
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "POST" "/api/authentication/oauth"
    Then the response status code should be "401"
    And I should get the json object:
    """
     {
     }
    """
  Scenario: Invalid resource owner should return an error
    Given I have the following JSON request body:
    """
      {
        "id_token": "ee.ee.ee",
        "resource_owner": "test"
      }
    """
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "POST" "/api/authentication/oauth"
    Then the response status code should be "422"
    And I should get the json object:
    """
     {
     }
    """
  Scenario: Request with missing params should return an error
    Given I have the following JSON request body:
    """
      {
        "id_token": "ee.ee.ee",
      }
    """
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "POST" "/api/authentication/oauth"
    Then the response status code should be "400"


  Scenario: Valid request with valid data should return JWT Token
    Given I have the following JSON request body:
    """
      {
        "id_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhcHBfaWQiOiIiLCJpc3N1ZWRfYXQiOjE1OTU5NjQyMjEsImV4cGlyZXNfYXQiOjE5MTE1NDAyMjEsImVtYWlsIjoidGVzdEBnbWFpbC5jb20iLCJ1c2VyX2lkIjoiMSIsIm5hbWUiOiJ0ZXN0dXNlciJ9.hmb7P_lqfr_JDH2RMweZsq1CFfNE-jsIni6DLgp3EVUvoJvn3vXMAQGp6ihNzLpzLCVGhnFcyYnHvENahfsPJoDV48Uvq4RiN0ckJgOM5GZmLX5pjDvozNsaU6w3MPG7nr2qTgK8lvfocdXzteIDYqrK9ClInwfWdMcirHr5UdguGttvEjPkicgOxzSNZggvSR4LiBFf7KQqbJ-InQFBBBQLxIUbRuOVKOWlY_mR9btfbdB-Q1AZUNhku9nt910QJ00mnoNsHRAbzTDliBQfD2ZYXqnHC8MJOZw9QNeo4_-QhKDkG1CKRAF5ImKn30nXcybSY1fJ_sRuMbe3o3Gdig",
        "resource_owner": "facebook"
      }
    """
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "POST" "/api/authentication/oauth"
    Then the response status code should be "200"
    And I should get the json object:
    """
     {
       "token": "REGEX_STRING_WILDCARD"
     }
    """

  Scenario: Expired id token should return an error
    Given I have the following JSON request body:
    """
      {
        "id_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhcHBfaWQiOiIiLCJpc3N1ZWRfYXQiOjE1OTU5NjQzNDYsImV4cGlyZXNfYXQiOjE1OTU5NjA3NDYsImVtYWlsIjoidGVzdEBnbWFpbC5jb20iLCJ1c2VyX2lkIjoiMSIsIm5hbWUiOiJ0ZXN0dXNlciJ9.MbIX-qfx4nw9Z5EUajiOnXrb1oqqOoA_ZKzXWgEDx_lLyBy6sVHf-nAkTIo3ID_XyEWtHtgUrtNwzwwPpQ3-zG64wwXkVV0D3wpHNq7RtxvpS3a_uTOR6XHl_35trijXxsvwE6psVnBaktWShiIfBUkuKxVd_GQYgw5kmrQgiOuyXQMbv4W-rH8fUiXAmNrEHBbw-nOyMp_9ECi62rlXyooJOK4mcgj2jC86maaJYXBd9eW3RakbmuehFofIGdIzNnkwOqpltpqGujzTfwzs94rR0mQTqiimN6MAr2WRen9cEkjWDwobd43loL6BSGlOehW8OujN6h4cbw3KNFAnlw",
        "resource_owner": "facebook"
      }
    """
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "POST" "/api/authentication/oauth"
    Then the response status code should be "401"
    And I should get the json object:
    """
     {
     }
    """

  Scenario: Id token with wrong signature should return an error
    Given I have the following JSON request body:
    """
      {
        "id_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhcHBfaWQiOiIiLCJpc3N1ZWRfYXQiOjE1OTU5NjQzNDYsImV4cGlyZXNfYXQiOjE1OTU5NjA3NDYsImVtYWlsIjoidGVzdEBnbWFpbC5jb20iLCJ1c2VyX2lkIjoiMSIsIm5hbWUiOiJ0ZXN0dXNlciJ9.MbIX-qfx4nw9Z5EUajiOnXrb1oqqOoA_ZKzXWgEDx_lLyBy6sVHf-nAkTIo3ID_XyEWtHtgUrtNwzwwPpQ3-zG64wwXkVV0D3wpHNq7RtxvpS3a_uTOR6XHl_35trijXxsvwE6psVnBaktWShiIfBUkuKxVd_GQYgw5kmrQgiOuyXQMbv4W-rH8fUiXAmNrEHBbw-nOyMp_9ECi62rlXyooJOK4mcgj2jC86maaJYXBd9eW3RakbmuehFofIGdIzNnkwOqpltpqGujzTfwzs94rR0mQTqiimN6MAr2WRen9cEkjWDwobd43loL6BSGlOehW8OujN6h4cbw3KNFBnlw",
        "resource_owner": "facebook"
      }
    """
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "POST" "/api/authentication/oauth"
    Then the response status code should be "401"
    And I should get the json object:
    """
     {
     }
    """

  Scenario: Id token with wrong app id should return an error
    Given I have the following JSON request body:
    """
      {
        "id_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhcHBfaWQiOiIxMjM0NTYiLCJpc3MiOiJjYXRyb2JhdCIsImlzc3VlZF9hdCI6MTU5ODE5NDQ5MCwiZXhwaXJlc19hdCI6MTU5ODE5ODA5MCwiZW1haWwiOiJ0ZXN0QGdtYWlsLmNvbSIsInVzZXJfaWQiOiIxIiwibmFtZSI6InRlc3R1c2VyIn0.nyDB0mMkDv-_Oi4PpFqLgExDR-sfLJv8iXXycvVn3e-Z9taCY7uu3yoh_HijRywzm5g4EeOBgTSkmJou6nPhRxprJBVLdHuLBAjXNvGyc2UO5VHuCbg2ZJIbEjfEHDnZD8z3fm4RMTFGo_1ZKu4nQB2KdduPfh7o0FXxKa2_ntz4pXaf02fq9TetFGpdBqU29Ar0Q5hwTaxdPfNonB-d4SYxq5RAI8hBx_N2inD5yptw493AvXCc_2FlnH-QTSREBTa-hVLpPCa_afDSBKCYdN0wTHkuOA0qL5ZLC27pRS9nMC1rlqmETTg2zfHiWShM0mnyNCNDeso0MiT0xfiI9Q",
        "resource_owner": "facebook"
      }
    """
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "POST" "/api/authentication/oauth"
    Then the response status code should be "401"
    And I should get the json object:
    """
     {
     }
    """
