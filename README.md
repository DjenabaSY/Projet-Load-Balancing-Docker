# Projet d'Authentification avec Load Balancing

Ce projet est une application web d'authentification utilisant PHP, MySQL, et Nginx comme load balancer. Il est conteneurisé avec Docker et inclut des outils de monitoring comme Prometheus et Grafana.
L'application comprend plusieurs pages essentielles :
 
Page de connexion (index.php)
 
Création de compte (register.php)

Tableau de bord (dashboard.php)

Messagerie (messages.php)
 
Profil (profile.php)

Notifications (notifications.php)

Paramètres (settings.php)

Déconnexion (loggout.php)
 
Le backend de l'application utilise un load balancing avec web1 et web2
pour assurer une meilleure répartition de la charge et améliorer les performances.
## Prérequis

- Docker
- Docker Compose
- Nginx
- PHP
- MySQL

## Structure du Projet

```
Projet-Load-Balancing-Docker/
│
├── src/ # Code source de l'application PHP
├── nginx/ # Configuration Nginx
├── prometheus/ # Configuration Prometheus
├── grafana/ # Configuration Grafana
├── elasticsearch/ # Configuration Elasticsearch
├── logstash/ # Configuration Logstash
├── kibana/ # Configuration Kibana
├── locust/ # Scripts de test de charge
│
├── Dockerfile # Dockerfile pour l'application PHP
├── docker-compose.yml # Configuration Docker Compose
└── composer.json # Dépendances PHP
```

## Installation et Démarrage

1. Clonez ce dépôt :
   git clone [URL_DU_REPO]
   cd Projet-Load-Balancing-Docker
   
2. Lancez les conteneurs avec Docker Compose :
   docker-compose up -d

##Configuration
 
Modifier les variables d'environnement dans .env.local si nécessaire.
 
Configurer Nginx si besoin.

## Services

- **Application PHP** : `http://localhost:8083` Accessible via le load balancer sur le port 8083
- **PHPMyAdmin** : `http://localhost:8080`
- **Prometheus** : `http://localhost:9090`
- **Grafana** : `http://localhost:3000`: usernme : admin, password : admin
- **Kibana** : `http://localhost:5601`
- **Locust** : `http://localhost:8089`

## Monitoring

- Les métriques de l'application sont exposées via le PHP-FPM Exporter.
- Prometheus collecte ces métriques.
- Grafana est utilisé pour visualiser les métriques.

## Logs

- Les logs de l'application sont envoyés à Elasticsearch via Logstash.
- Kibana est utilisé pour visualiser et analyser les logs.

## Tests de Charge

Locust est inclus pour effectuer des tests de charge. Accédez à l'interface Locust pour configurer et exécuter les tests.

## Contribution

Les contributions à ce projet sont les bienvenues. Veuillez suivre ces étapes :

1. Forkez le projet
2. Créez votre branche de fonctionnalité (`git checkout -b feature/AmazingFeature`)
3. Committez vos changements (`git commit -m 'Add some AmazingFeature'`)
4. Poussez vers la branche (`git push origin feature/AmazingFeature`)
5. Ouvrez une Pull Request

## Licence

Ce projet est sous licence MIT.
 
Copyright (c) 2025 esic-students

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:
 
The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.
 
THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
 


