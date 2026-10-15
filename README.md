# Library API

Laravel 12 project matching the supplied four-table diagram. Column and table names are preserved. tblBookAuthor uses the composite primary key (BookID, AuthorID); there are no extra timestamp columns on these four tables.

## Run
```sh
composer install
php artisan migrate
php artisan serve
```
The default database is SQLite (database/database.sqlite). To use MySQL, configure DB_CONNECTION=mysql and DB_HOST, DB_PORT, DB_DATABASE=library, DB_USERNAME and DB_PASSWORD in .env, create the library database, then migrate.

Send `Accept: application/json` and `Content-Type: application/json`.

| Method | URL | Controller method |
|---|---|---|
| GET | /api/authors | index |
| POST | /api/authors | store |
| GET | /api/authors/{id} | show |
| PUT/PATCH | /api/authors/{id} | update |
| DELETE | /api/authors/{id} | destroy |

The same five methods apply to `/api/book-types` and `/api/books`. For `/api/book-authors`, listing and creation use the collection URL, while show/update/delete use `/api/book-authors/{BookID}/{AuthorID}`. Update changes AuthorDate and Remark; to change the association, delete and create it again.

Create records in this order:
```json
POST /api/authors: {"AuthorName":"Sok Dara","Gender":"Male"}
POST /api/book-types: {"BookTypeName":"Programming"}
POST /api/books: {"BookTitle":"Laravel Basics","BookTypeID":1,"NumOfPages":200,"NumOfCopies":5}
POST /api/book-authors: {"BookID":1,"AuthorID":1,"AuthorDate":"2026-10-14","Remark":"Main author"}
```
Required fields: AuthorName, BookTypeName, BookTitle/BookTypeID and BookID/AuthorID. Other fields are optional. Photo stores a path or URL. Validation failures return 422, missing records 404, creation 201 and deletion 204. Referenced parents cannot be deleted until dependent records are removed (409). These assignment endpoints are public and have no authentication.
