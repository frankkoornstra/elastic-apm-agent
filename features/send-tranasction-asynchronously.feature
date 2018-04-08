Feature: Send transactions asynchronously
  In order to minimize the execution time
  As a library user
  I want to asynchronously send transactions to the APM server

  Scenario: send valid transaction
    Given agent "alloy" with version "1"
      And the service "focus"
      And a transaction with id "18C537FC-80D0-4CAD-8CED-965347A42B80" and name "manual-scan" and duration "15.3" and type "scan" that started at "2018-04-07"
     When I send the transactions asynchronously
     Then all asynchronously sent transactions are accepted

  Scenario: send invalid transaction
    Given agent "alloy" with version "1"
      And the service "focus"
    Given an invalid transaction
     When I send the transactions asynchronously
     Then an asynchronously sent transaction fails
