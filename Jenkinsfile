pipeline {
    agent any
    stages {
        stage("Build") {
            steps { sh './install.sh' }
        }
        stage("Unit test") {
            steps { sh 'php artisan test' }
        }
        stage("Code Coverage") {
            steps { sh "vendor/bin/phpunit --coverage-html 'reports/coverage'" }
        }
        stage("Static code analysis larastan") {
            steps { sh "vendor/bin/phpstan analyse --memory-limit=2G" }
        }
        stage("Static code analysis phpcs") {
            steps { sh "vendor/bin/phpcs" }
        }
        stage("Docker build") {
            steps {
                sh "docker rmi cscd488/janus"
                sh "docker build -t cscd488/janus --no-cache ."
            }
        }
        stage("Deploy to port 8000") {
            steps {
                sh "docker ps | egrep -q janus-nightly && docker stop janus-nightly || true"
                sh "docker container rm janus-nightly || true"
                sh "docker run --rm -p 8080:8080 --name janus-nightly cscd488/janus"
            }
        }
    }
}
