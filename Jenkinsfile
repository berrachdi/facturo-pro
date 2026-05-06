pipeline {
    agent any

    environment {
        COMPOSE_FILE = 'docker/docker-compose.yml'
        APP_URL      = 'http://localhost:8080'
    }

    stages {

        stage('Checkout') {
            steps {
                checkout scm
                echo "Branch: ${env.GIT_BRANCH} — Build #${env.BUILD_NUMBER}"
            }
        }

        stage('Lint PHP') {
            steps {
                sh '''
                    echo "=== Vérification syntaxe PHP ==="
                    find src/ -name "*.php" -exec php -l {} \\; | grep -v "No syntax errors"
                    find views/ -name "*.php" -exec php -l {} \\; | grep -v "No syntax errors"
                    echo "Syntaxe OK"
                '''
            }
        }

        stage('Build') {
            steps {
                sh '''
                    echo "=== Build image Docker PHP ==="
                    docker compose -f $COMPOSE_FILE build php
                '''
            }
        }

        stage('Deploy') {
            steps {
                sh '''
                    echo "=== Déploiement ==="
                    docker compose -f $COMPOSE_FILE up -d --remove-orphans
                '''
            }
        }

        stage('Health Check') {
            steps {
                sh '''
                    echo "=== Vérification santé app ==="
                    sleep 5
                    STATUS=$(curl -s -o /dev/null -w "%{http_code}" $APP_URL/login)
                    if [ "$STATUS" != "200" ]; then
                        echo "Health check échoué — HTTP $STATUS"
                        exit 1
                    fi
                    echo "App accessible — HTTP $STATUS ✅"
                '''
            }
        }
    }

    post {
        success {
            echo "✅ Pipeline réussi — Build #${env.BUILD_NUMBER} déployé"
        }
        failure {
            echo "❌ Pipeline échoué — Build #${env.BUILD_NUMBER}"
            sh 'docker compose -f $COMPOSE_FILE logs --tail=50 || true'
        }
        always {
            echo "Durée totale : ${currentBuild.durationString}"
        }
    }
}
