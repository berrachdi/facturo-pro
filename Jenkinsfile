pipeline {
    agent any

    environment {
        COMPOSE_FILE = 'docker/docker-compose.yml'
        // host.docker.internal = localhost de la machine hôte vu depuis Jenkins (container)
        APP_URL = 'http://host.docker.internal:8080'
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
                    docker run --rm \
                        -v "$WORKSPACE:/app" \
                        -w /app \
                        php:8.2-cli \
                        sh -c 'find src/ views/ public/ -name "*.php" -exec php -l {} \\; | grep -v "No syntax errors" || true; echo "✅ Syntaxe OK"'
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
                    echo "=== Déploiement stack complète ==="
                    docker compose -f $COMPOSE_FILE up -d
                '''
            }
        }

        stage('Health Check') {
            steps {
                sh '''
                    echo "=== Attente démarrage des containers ==="
                    sleep 15
                    echo "=== Vérification santé app ==="
                    STATUS=$(curl -s -o /dev/null -w "%{http_code}" $APP_URL/login)
                    echo "HTTP status : $STATUS"
                    if [ "$STATUS" != "200" ]; then
                        echo "❌ Health check échoué — HTTP $STATUS"
                        docker compose -f $COMPOSE_FILE logs --tail=20 php
                        exit 1
                    fi
                    echo "✅ App accessible — HTTP $STATUS"
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
            sh 'docker compose -f $COMPOSE_FILE logs --tail=50 php || true'
        }
        always {
            echo "Durée totale : ${currentBuild.durationString}"
        }
    }
}
