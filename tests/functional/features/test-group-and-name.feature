Feature: Test Group and Name Command
    In order to validate the unique constraint functionality for multiple fields
    As a developer
    I want to run the app:test-group-and-name command and ensure it behaves correctly

    Scenario: Running the test group and name command
        When I run the console command "app:test-group-and-name"
        Then the command should succeed