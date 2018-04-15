Feature: Enrich open transaction
  In order to capture additional spans in a transaction
  As a library user
  I want to enrich the open transaction

  Scenario: add span for cache calls
    Given a default open transaction
      And I get item "behemoth" from cache
     When I close the open transaction
     Then the closed transaction has a cache span for getting item "behemoth"

  Scenario: add span for http requests
    Given a default open transaction
      And I send an http request for "http://gaia.prime"
     When I close the open transaction
     Then the closed transaction has an http request span for "http://gaia.prime"
