Feature: Test Group Command
    In order to validate the unique constraint functionality for single field
    As a developer
    I want to run the app:test-group command and ensure it behaves correctly

    Scenario: Running the test group command
        When I run the console command "app:test-group"
        Then the command should succeed