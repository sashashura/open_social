@api @private-message @DS-4372 @DS-5206 @stability-3 @create-private-message
  Feature: Create Private Message
    Benefit: Sending private messages to other users on the platform.
    Role: As a Verified
    Goal/desire: Let users collaborate more and in private through the platform.

  # Send a message to another user.
  Scenario: Successfully send a private message to another user.
    Given I enable the module "social_private_message"
    Given users:
      | name          | mail                  | status | created    | roles    |
      | PM User One   | pm_user_1@example.com | 1      | 1861920000 | verified |
      | PM User Two   | pm_user_2@example.com | 1      | 1893456000 | verified |
    When I am logged in as "PM User One"
    And I am on "/user/inbox"
    Then I should see "You do not have any private messages yet. Click on the button on the right to start a new message."
    And I click "New message"
    Then I should see "Create Private Message"
    And I fill in "Member(s)" with "PM User" and select "PM User Two"
    And I fill in "Message" with "Hi PM User Two, I heard you like pineapple on your pizza..."
    And I press "Send"
    Then I should see "Your message has been created."

    # I want to send a new message from a user`s profile teaser
    When I am on "/all-members"
    Then I should see "Private message"
    When I click the xth "1" link with the text "Private message"
    Then I should see "PM User Two"
    And I fill in "Message" with "Hi PM User Two, I heard you like salami on your pizza..."
    And I press "Send"
    Then I should see "Your message has been created."

    # I want to send a new message from a user`s profile
    And the cache has been cleared
    When I am on the profile of "PM User Two"
    Then I should see the link "Private message"
    And I click "Private message"
    Then I should see "PM User Two"
    And I should see the button "Send"
    And I should see "You"
    When I fill in "Message" with "Hi PM User Two, are we going to eat some pizza tomorrow?"
    And I press "Send"
    Then I should see "Your message has been created."

    # I want to see my new message and reply.
    Given I am logged in as "PM User Two"
    And I am on "/user/inbox"
    Then I should see "Inbox"
    And I should see the link "View thread"
    And I should see "PM User One"
    When I click "View thread"
    Then I should see "Hi PM User Two, are we going to eat some pizza tomorrow?"
    When I fill in "Message" with "Hey PM User One, ...That's fine. I will order!"
    And I press "Send"
    Then I should see "Your message has been created."

    # Delete the thread.
    When I click "View thread"
    # And I click the xth "0" element with the css ".dropdown-toggle" in the "Main content"
    # Then I click "Delete thread"
    # @TODO It is hard to find a reason why commented step above sometimes
    # fails, so let's temporarily delete the thread by going to the delete
    # page.
    And I am on "/private-messages/1/delete"
    And I should see "This action cannot be undone."
    And I press "Delete thread"
    Then I should see "Your message has been deleted."
    And I should see "You do not have any private messages yet. Click on the button on the right to start a new message."

    # LU should not be able to create messages.
    Given I disable that the registered users to be verified immediately
    When I am logged in as an "authenticated user"
      And I am on "/user/inbox"
    Then I should see "Access denied"
      And I should see "You are not authorized to access this page."
      And I enable that the registered users to be verified immediately

  # Create thread with multiple users.
  Scenario: Create thread with multiple users.
    Given I enable the module "social_private_message"
    Given users:
      | name          | mail                  | status | roles    |
      | PM User One   | pm_user_1@example.com | 1      | verified |
      | PM User Two   | pm_user_2@example.com | 1      | verified |
      | PM User Three | pm_user_3@example.com | 1      | verified |
      | PM User Four  | pm_user_4@example.com | 1      | verified |
    Given I am logged in as "PM User Four"
    When I am on "/user/inbox"
    And I click "New message"
    Then I fill in "Member(s)" with "PM User" and select "PM User One"
    And I fill next in "Member(s)" with "PM User" and select "PM User Two"
    And I fill in "Message" with "Hi, let's discuss what pizza's we're gonna order!"
    Then I press "Send"
    Then I should see "Your message has been created."

    # Check that all the users in the thread received the message.
    When I am logged in as "PM User One"
    And I am on "/user/inbox"
    Then I should see "Hi, let's discuss what pizza's we're gonna order!"
    And I click "View thread"
    And I fill in "Message" with "I'd like a pizza with tuna."
    And I press "Send"

    When I am logged in as "PM User Two"
    And I am on "/user/inbox"
    Then I should see "PM User One: I'd like a pizza with tuna."
    And I click the xth "0" element with the css ".unread-thread"
    And I fill in "Message" with "OMG YES, I want a pizza hawai with extra ansjovis on top!"
    And I press "Send"

    # Multiple messages in the inbox.
    When I am logged in as "PM User One"
    When I am on "/user/inbox"
    And I click "New message"
    And I fill in "Member(s)" with "PM User" and select "PM User Four"
    And I fill in "Message" with "Be strict on the pizza toppings, user two likes it weird!"
    When I press "Send"

    # Check the two messages.
    Given I am logged in as "PM User Four"
    When I am on "/user/inbox"
    Then I should see "Be strict on the pizza toppings, user two likes it weird!"
    And I should see "PM User Two: OMG YES, I want a pizza hawai with extra ansjovis on top!"
