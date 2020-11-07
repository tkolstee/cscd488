pipeline {
  agent any
  stages {
    stage("Build") {
      steps {
        sh './install.sh'
      }
    }
    stage("Unit test") {
      steps {
        sh 'php artisan test'
      }
    }
    stage("Code Coverage") {
      steps {
        sh "vendor/bin/phpunit --coverage-html 'reports/coverage'"
      }
    }
  }
}
