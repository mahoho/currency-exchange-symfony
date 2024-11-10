# Currency Exchange Demo App
Written using Symfony 7.  

## Setup
- Start database server with `docker-compose up -d`
- Create empty database with `symphony console doctrine:database:create`
- Create database structure with `symphony console doctrine:migrations:migrate`
- Populate rate sources with `symphony console doctrine:fixtures:load --group=rates_sources --append`


