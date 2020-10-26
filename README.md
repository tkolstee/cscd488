# cscd488

to build docker image: <br>
``` docker-compose build ``` <br>
to run dev server <br>
``` docker-compose up -d ``` <br>
to run unit tests <br>
``` docker-compose run cscd488 phpunit ``` <br>
to run commands in a new instance of the container <br>
``` docker-compose run cscd488 _command_ ``` <br>
to run commands in the running instance of the container (from up -d) <br>
``` docker-compose exec cscd488 _command_ ``` <br>