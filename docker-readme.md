## How to Run migrate Commands Outside src folder,Commands that depend on the Docker environment 

``
docker-compose exec app php artisan <command>
``

### app → The name of the service defined in your compose.yml (your Laravel/PHP container).