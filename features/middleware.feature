Feature: Middleware
  In order to automatically send transactions and errors to the APM server
  As a library user
  I want to use middleware handle requests and responses

  Scenario: sent transaction for request
    Given I add the transaction middleware to my stack
     When I send the default server request
     Then the transaction sent by middleware is accepted
