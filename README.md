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
- Write tests
- Add versioning to the API
- Add authorization features to the API
- Serve API though HTTPS only
- Add make setup target
- Add documentation
- Add githook to run phpstan and phpcbf on commit
- Remove hardcoded url for webserver from api tests
- Add validation and error-handling to routes (https://jsonapi.org/format/#error-objects)
- Sanitize output to prevent corruption
- Adher to JSON:API standards

## References
- https://levelup.gitconnected.com/how-to-properly-handle-requests-with-symfony-6-3-0bfc8d7726a9
