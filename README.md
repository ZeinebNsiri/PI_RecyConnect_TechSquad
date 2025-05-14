
# <img src="public/frontOffice/img/mainlogo.png" alt="RecyConnect Logo" width="40" style="vertical-align:middle;"> RecyConnect – Application Web

![Recyclé](https://img.shields.io/badge/Made%20with-Recycled%20Materials-4CAF50?style=flat&logo=recycle&logoColor=white&labelColor=1B5E20)
![Éco-responsable](https://img.shields.io/badge/Environment-Éco--responsable-2E7D32?style=flat&logo=leaf&logoColor=white&labelColor=004D40)

**RecyConnect** est une application web conçue pour établir un lien efficace entre différents acteurs du domaine de la gestion durable des déchets, notamment :

- les **supermarchés**
- les **restaurants**
- les **associations**
- les **usines de recyclage**
- les **particuliers**

## 🎯 Objectifs

L'objectif principal de RecyConnect est de **promouvoir la réutilisation et le recyclage** à travers :

- l’**échange de produits** entre producteurs et valorisateurs,
- la **publication de demandes** de récupération ou de valorisation de déchets,
- la **sensibilisation** du public grâce à des **événements** et **formations**.

## 🌱 Démarche éco-responsable

Ce projet s’inscrit dans une approche durable en contribuant :

- à la **réduction des déchets**,
- à l’encouragement d’une **économie circulaire**,
- et à l’atteinte des **Objectifs de Développement Durable (ODD)**
---
## 🎯 Objectifs de Développement Durable (ODD)

RecyConnect s’inscrit pleinement dans une démarche éco-responsable en répondant à plusieurs **Objectifs de Développement Durable (ODD)** définis par les Nations Unies :

<table>
  <tr>
    <td align="center">
      <img src="https://sdgs.un.org/sites/default/files/goals/E_SDG_Icons-08.jpg" width="80"/><br/>
      <strong>ODD 8</strong><br/>
      Travail décent et croissance économique
    </td>
    <td align="center">
      <img src="https://sdgs.un.org/sites/default/files/goals/E_SDG_Icons-11.jpg" width="80"/><br/>
      <strong>ODD 11</strong><br/>
      Villes et communautés durables
    </td>
    <td align="center">
      <img src="https://sdgs.un.org/sites/default/files/goals/E_SDG_Icons-12.jpg" width="80"/><br/>
      <strong>ODD 12</strong><br/>
      Consommation et production responsables
    </td>
    <td align="center">
      <img src="https://sdgs.un.org/sites/default/files/goals/E_SDG_Icons-13.jpg" width="80"/><br/>
      <strong>ODD 13</strong><br/>
      Lutte contre le changement climatique
    </td>
  </tr>
</table>

## ✨ Fonctionnalités principales

- 🔐 **Gestion des utilisateurs**
  - Création de compte avec reconnaissance faciale
  - Authentification sécurisée
  - Activation/désactivation de comptes par l'administrateur

- 📦 **Gestion des articles**
  - Ajout, modification, suppression et catégorisation des articles
  - Modération des images avec API Sightengine
  - Notification par email en cas de refus

- 🛒 **Gestion des commandes**
  - Ajout au panier, modification des quantités, suppression
  - Paiement via Paymee ou à la livraison
  - Génération de factures PDF

- 📝 **Gestion des posts (Forum)**
  - Création de publications avec médias
  - Filtrage par tags
  - Likes, commentaires, réponses imbriquées
  - Validation de contenu avec Gemini API

- 📆 **Gestion des événements**
  - Création, modification et suppression d'événements (en ligne ou sur site)
  - Intégration avec Jitsi Meet pour les visioconférences
  - Affichage de la carte avec Leaflet et géolocalisation via Nominatim

- 🧪 **Gestion des workshops**
  - Ajout, modification, suppression de workshops
  - Attribution de notes (1 à 5), filtrage, affichage des moyennes
  - Statistiques par catégorie, chatbot intégré, vidéo explicative
  - Génération automatique de description via analyse vidéo (IA)

---

## 🧰 Technologies utilisées

### 👨‍💻 Backend & Frontend

- **Symfony (PHP)** – Framework backend MVC puissant et maintenable
- **Twig** – Moteur de templates intégré à Symfony
- **Bootstrap** – Framework CSS pour un design responsive et moderne
- **JavaScript** – Pour l’interactivité côté client
- **MySQL** – Base de données relationnelle pour le stockage des données

### 🔌 APIs & Intégrations externes

- **[reCAPTCHA](https://www.google.com/recaptcha/about/)** – Protection contre les bots et les soumissions frauduleuses
- **[MailerSend](https://www.mailersend.com/)** – Envoi d’emails transactionnels (validation, notifications, etc.)
- **Chatbot intégré** – Interaction automatisée avec les utilisateurs
- **[Paymee](https://sandbox.paymee.tn/)** – Paiement sécurisé en ligne

### 🧠 Intelligence Artificielle & IA appliquée

- **Chatbot intelligent** – Réponses automatiques aux questions fréquentes
- **Analyse vidéo par IA** – Génération automatique de résumés descriptifs pour les workshops

---


## 📚 Projet académique

Ce projet a été réalisé dans le cadre d’un **projet académique** au sein de l’école d’ingénierie **ESPRIT** (École Supérieure Privée d'Ingénierie et de Technologies).  
Il démontre la capacité à concevoir une solution web complète, fonctionnelle et engagée, en intégrant des technologies avancées telles que **Symfony**, des **API d’intelligence artificielle** et des outils axés sur le **développement durable**.


---

## 👥 Équipe projet – TechSquad

Ce projet a été réalisé dans le cadre d’un projet académique à l’école **ESPRIT**, par un groupe de 6 étudiantes en ingénierie informatique, passionnées par l’innovation durable et l’intelligence artificielle.

**Membres de l'équipe : TechSquad**
- Sahar Mnif  
- Zeineb Nsiri  
- Mohamed Aziz Zouari
- Samar Touil
- Amal Eljazi
- Eya Guirat

Nous avons collaboré sur toutes les étapes du projet : conception, développement, intégration d'API et documentation. Ce travail reflète notre engagement pour un avenir plus vert et plus intelligent 🌍🤖.

## 🏁 Lancement du projet

1. **Cloner le dépôt**
   ```bash
   git clone https://github.com/ZeinebNsiri/PI_RecyConnect_TechSquad.git
   ```

---
