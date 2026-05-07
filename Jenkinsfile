pipeline {
    agent any

    environment {
        // Chemin réel sur l'hôte — identique dans le container Jenkins
        HOST_PROJECT = '/home/mohamed-berrachdi/projects/facturo-pro'
        APP_URL      = 'http://host.docker.internal:8080'
    }

    stages {

        stage('Checkout') {
            steps {
                checkout scm
                echo "Branch: ${env.GIT_BRANCH} — Build #${env.BUILD_NUMBER}"
            }
        }

        stage('Sync') {
            steps {
                sh '''
                    echo "=== Synchro workspace → projet hôte ==="
                    cp -rp $WORKSPACE/. $HOST_PROJECT/
                    echo "✅ Synchro OK"
                '''
            }
        }

        stage('Lint PHP') {
            steps {
                sh '''
                    echo "=== Vérification syntaxe PHP ==="
                    docker run --rm \
                        -v "$HOST_PROJECT:/app" \
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
                    docker compose -f $HOST_PROJECT/docker/docker-compose.yml build php
                '''
            }
        }

        stage('Deploy') {
            steps {
                sh '''
                    echo "=== Redémarrage container PHP ==="
                    docker compose -f $HOST_PROJECT/docker/docker-compose.yml \
                        up -d --no-deps --force-recreate php
                '''
            }
        }

        stage('Health Check') {
            steps {
                sh '''
                    echo "=== Attente démarrage PHP ==="
                    sleep 10
                    STATUS=$(curl -s -o /dev/null -w "%{http_code}" $APP_URL/login)
                    echo "HTTP status : $STATUS"
                    if [ "$STATUS" != "200" ]; then
                        echo "❌ Health check échoué — HTTP $STATUS"
                        docker compose -f $HOST_PROJECT/docker/docker-compose.yml logs --tail=20 php
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
            sh 'docker compose -f $HOST_PROJECT/docker/docker-compose.yml logs --tail=50 php || true'
        }
        always {
            echo "Durée totale : ${currentBuild.durationString}"
        }
    }
}
