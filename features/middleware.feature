Feature: Middleware
  In order to automatically send transactions and errors to the APM server
  As a library user
  I want to use middleware handle requests and responses

  Scenario: sent transaction for request
    Given I add the transaction middleware to my stack
     When I send the default server request
     Then the transaction sent by middleware is accepted

  Scenario: sent errors when exceptions occur
    Given I add the error middleware to my stack
     When I send a server request that throws an exception
     Then the error sent by the middleware is accepted

  Scenario: enrich open transaction based on the request
    Given I add the transaction middleware to my stack
      And I add the open transaction request enrichment middleware to my stack
     When I send the default server request
     Then the open transaction is enriched with request data

  Scenario: enrich open transaction based on the response
    Given I add the transaction middleware to my stack
      And I add the open transaction response enrichment middleware to my stack
     When I send the default server request
     Then the open transaction is enriched with response data
