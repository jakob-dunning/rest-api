# Coding challenge

This is a simple PHP project to display the skills of the coder.

The challenge is to build a REST-Api for a shopping cart in PHP/Symfony which fulfils the following criteria:

It must be possible to:
- Add an item to the shopping cart 
- Remove an item from the shopping cart
- Edit an item in the shopping cart
- Display the shopping cart

## Todo

- Don't run nginx, postgres or php as root inside docker containers
- Serve API though HTTPS only
- Add documentation
- Remove hardcoded url for webserver from api tests
- Sanitize output to prevent corruption
- Adher to JSON:API standards
- Implement HEAD?
- Replace returning error responses in controllers with exceptions and handle formatting in eventsubscriber
- Implement Etag
- Add caching headers
- test all routes are inaccessible without autorization
- Send location in location header on POST and PATCH? method

## References
- https://levelup.gitconnected.com/how-to-properly-handle-requests-with-symfony-6-3-0bfc8d7726a9
