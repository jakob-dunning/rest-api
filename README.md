# Coding challenge

This is a simple PHP project to display the skills of the coder.

The challenge is to build a REST-Api for a shopping cart in PHP/Symfony which fulfils the following criteria:

It must be possible to:
- Add an item to the shopping cart 
- Remove an item from the shopping cart
- Edit an item in the shopping cart
- Display the shopping cart

## Usage
### Environment
Clone this repository to your local machine and run `make setup` in the root directory.
This will set up the development environment available at `http://localhost`.
Example data can be added to the database by running `make load-fixtures`.

### Format
The API accepts JSON and returns the content type `application/vnd.api+json`. The specification can be found at https://jsonapi.org/.

### Authorization
Authorization is achieved through JSON Web Tokens. A token is obtained by a `POST` request to `http://localhost/api/login_check`
with `{"username": "test@test.com", "password":"test"}` set to the body.
On consecutive requests, the resulting token must be added to an Authorization header in the form of `Authorization: Bearer <token>`.

### Endpoints
- GET /api/products/v1 - Show all products.
- GET /api/products/v1/<product-id> - Show a single product.
- POST /api/products/v1 - Create a new product with e.g. `{
  "id": "44682a67-fa83-4216-9e9d-5ea5dd5bf480",
  "type": "Tablet",
  "manufacturer": "Lenovo",
  "model": "Tab M9",
  "price": 19900
  }` set to the body of the request.
- PATCH /api/products/v1/<product-id> - Update product properties through JSON patch requests e.g. `{"op":"replace",
  "path":"/expiresAt",
  "value":"$expiresAt"}`.
- DELETE /api/products/v1/<product-id> - Remove a product.

- GET /api/shopping-carts/v1/<cart-id> - Show a shopping cart.
- POST /api/shopping-carts/v1 - Create a new shopping cart.
- POST /api/shopping-carts/v1/<cart-id>/products - Add a product to the shopping cart with the body set to the product id e.g. `{"id":"44682a67-fa83-4216-9e9d-5ea5dd5bf480"}`.
- PATCH /api/shopping-carts/v1/<cart-id> - Update shopping cart properties through JSON patch requests
- DELETE /api/shopping-carts/v1/<cart-id> - Delete a shopping cart.
- DELETE /api/shopping-carts/v1/<cart-id>/products/<product-id> - Remove a product from a shopping cart.

## Todo

- Don't run nginx, postgres or php as root inside docker containers
- Serve API though HTTPS only
- Extract hardcoded url from api tests to environment variable
- Implement HEAD
- Implement Etag
- Add caching headers
- Test all routes are inaccessible without autorization
- remove all static constructors unless I really need them
