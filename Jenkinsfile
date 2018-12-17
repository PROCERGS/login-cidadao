pipeline {
    agent { label 'php' }

    stages {
        //stage('Clean') {
        //    steps {
        //        cleanWs()
        //    }
        //}
        stage('Build') {
            steps {
                sh 'composer install --no-progress --no-interaction --no-scripts --prefer-dist'
            }
        }
        stage('Run Tests') {
            steps {
                sh 'php -i | grep debug'
                sh 'composer test -- --log-junit=junit.xml'
                //sh 'composer test -- --coverage-clover=clover.xml --log-junit=junit.xml'
            }
        }
        stage('SonarQube analysis') {
            steps {
                script {
                    scannerHome = tool 'SonarQube Scanner 2.8'
                }
                withSonarQubeEnv('sonar_procergs') {
                    sh "${scannerHome}/bin/sonar-scanner"
                }
            }
        }
        //stage('Quality Gate') {
        //    steps {
        //        timeout(time: 5, unit: 'MINUTES') {
        //            waitForQualityGate abortPipeline: true
        //        }
        //    }
        //}
        // Artifactory examples:
        // https://github.com/jfrog/project-examples/tree/master/jenkins-examples/pipeline-examples
        stage('Archive') {
            steps {
                sh 'composer archive --format=zip --dir=dist'
                archiveArtifacts artifacts: 'dist/*.zip'

                script {
                    def artifactory = Artifactory.server('artifactory')
                    def uploadSpec = """{
                        "files": [{
                            "pattern": "dist/*.zip",
                            "target": "php-local/procergs/login-cidadao/"
                        }]
                    }"""
                    artifactory.upload(uploadSpec)
                }
            }
        }
    }
    post {
        always {
            deleteDir()
        }
    }
}
