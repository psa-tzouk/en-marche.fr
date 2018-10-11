@api
Feature:
  In order to get adherents' information
  As a referent
  I should be able to acces adherents API data

  Background:
    Given I freeze the clock to "2018-04-17"
    And the following fixtures are loaded:
      | LoadUserData                       |
      | LoadAdherentData                   |
      | LoadEmailSubscriptionHistoryData   |
      | LoadCommitteeMembershipHistoryData |
      | LoadClientData                     |

  Scenario: As a non logged-in user I can not access the adherents count information
    When I am on "/api/statistics/adherents/count"
    Then the response status code should be 401

  Scenario: As an adherent I can not access the adherents count information
    When I am logged as "jacques.picard@en-marche.fr"
    And I am on "/api/statistics/adherents/count"
    Then the response status code should be 401

  Scenario: As a referent I can access the adherents count information
    Given I add "Accept" header equal to "application/json"
    And I send a "POST" request to "/oauth/v2/token" with parameters:
      | key           | value                                        |
      | client_secret | crOsk2OxtYb4CgnKoYvhb9wvO73QLYyccChiFrV9evE= |
      | client_id     | 4f3394d4-7137-424a-8c73-27e0ad641fc9         |
      | grant_type    | client_credentials                           |
      | scope         | read:stats                                   |
    And I add the access token to the Authorization header
    When I send a "GET" request to "/api/statistics/adherents/count?email=referent@en-marche-dev.fr"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "female":7,"male":13,"total":20
    }
    """

  Scenario: As a non logged-in user I can not access the managed by referent adherents count information
    When I am on "/api/statistics/adherents/count-by-referent-area"
    Then the response status code should be 401

  Scenario: As an adherent I can not access the managed by referent adherents count information
    When I am logged as "jacques.picard@en-marche.fr"
    And I am on "/api/statistics/adherents/count-by-referent-area"
    Then the response status code should be 401

  Scenario: As a referent I can access the managed by referent adherents count information
    Given I add "Accept" header equal to "application/json"
    And I send a "POST" request to "/oauth/v2/token" with parameters:
      | key           | value                                        |
      | client_secret | crOsk2OxtYb4CgnKoYvhb9wvO73QLYyccChiFrV9evE= |
      | client_id     | 4f3394d4-7137-424a-8c73-27e0ad641fc9         |
      | grant_type    | client_credentials                           |
      | scope         | read:stats                                   |
    And I add the access token to the Authorization header
    When I send a "GET" request to "/api/statistics/adherents/count-by-referent-area?email=referent-75-77@en-marche-dev.fr"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "female":3,
      "male":5,
      "total":8,
      "adherents": [
          {"date": "2018-04", "total": 7},
          {"date": "2018-03", "total": 7},
          {"date": "2018-02", "total": 7},
          {"date": "2018-01", "total": 7},
          {"date": "2017-12", "total": 6},
          {"date": "2017-11", "total": 6}
      ],
      "committee_members": [
          {"date": "2018-04", "count": 3},
          {"date": "2018-03", "count": 3},
          {"date": "2018-02", "count": 2},
          {"date": "2018-01", "count": 2},
          {"date": "2017-12", "count": 2},
          {"date": "2017-11", "count": 2}
      ],
      "email_subscriptions": [
          {"date": "2018-04", "subscribed_emails_local_host": 8, "subscribed_emails_referents": 8},
          {"date": "2018-03", "subscribed_emails_local_host": 0, "subscribed_emails_referents": 0},
          {"date": "2018-02", "subscribed_emails_local_host": 4, "subscribed_emails_referents": 0},
          {"date": "2018-01", "subscribed_emails_local_host": 3, "subscribed_emails_referents": 0},
          {"date": "2017-12", "subscribed_emails_local_host": 2, "subscribed_emails_referents": 0},
          {"date": "2017-11", "subscribed_emails_local_host": 1, "subscribed_emails_referents": 0}
      ]
    }
    """

  Scenario: As an anonymous user I cannot access to my information
    And I am on "/api/users/me"
    Then the response status code should be 401

  Scenario: As a referent I can access to my information
    When I am logged as "referent-75-77@en-marche-dev.fr"
    And I am on "/api/users/me"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "uuid": "2f69db3c-ecd7-4a8a-bd23-bb4c9cfd70cf",
      "managedAreaTagCodes": [
        "75008",
        "75009",
        "75",
        "77"
      ],
      "country": "FR",
      "zipCode": "75001",
      "emailAddress": "referent-75-77@en-marche-dev.fr",
      "firstName": "Referent75and77",
      "lastName": "Referent75and77"
    }
    """

  Scenario: As a standard adherent I can access to my information
    When I am logged as "jacques.picard@en-marche.fr"
    And I am on "/api/users/me"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "uuid":"a046adbe-9c7b-56a9-a676-6151a6785dda",
      "country":"FR",
      "zipCode":"75008",
      "emailAddress":"jacques.picard@en-marche.fr",
      "firstName":"Jacques",
      "lastName":"Picard"
    }
    """
