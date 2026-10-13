# Shelf — brochure commerciale et technique

**CANCELLED historical draft: owner excludes France from planned App Store availability. Do not send, file or complete this document.**

**BROUILLON — NON DÉPOSÉ — sans récépissé ni attestation officielle.**
Préparé le 13 octobre 2026; inventaire déjà vérifié, pas de nouveau test d'achat.

## Identification et brochure commerciale

Produit : **Shelf - شیلف**, marque Shelf; application iOS **1.0.0 (4)**;
identifiant services.shelf.app; identifiant Apple 6821471140.
Source de la version : 2702fbbe2083ca8adf2a20ab977fe2e479af3c8b.
Responsable du projet : Ajmal Aand; coordonnées à compléter dans la copie privée.
Site : https://shelf.services. Distribution envisagée : App Store, dont la France.
Date de commercialisation publique : non fixée; lancement public suspendu.
Distribution actuelle : version TestFlight interne, traitement Apple VALID,
usage interne bloqué par MISSING_EXPORT_COMPLIANCE au dernier contrôle.

Shelf est une librairie numérique et un lecteur de livres en pachto et farsi.
Le téléchargement de l'application est gratuit; les livres sont proposés à l'unité.
Le catalogue est administré par le propriétaire. Le lecteur peut consulter un
extrait gratuit, puis lire les ouvrages auxquels son compte possède un droit
vérifié. La bibliothèque permet la lecture hors ligne sous contrôle périodique.
Aucune activation d'achat réel n'est incluse dans ce dossier.

## Brochure technique — fonction et périmètre

Nature : logiciel pour iOS, minimum iOS 15.0. Flutter/Dart avec services natifs.
Fonction principale : librairie/lecture de contenus éditoriaux; la cryptographie
soutient l'accès autorisé aux livres. Pas d'outil général de chiffrement de fichiers,
pas de messagerie chiffrée proposée par Shelf, pas de choix d'algorithme ou de clé
fourni au lecteur. L'absence de ces interfaces n'est pas une certification du logiciel.

| Fonction | Mise en œuvre vérifiée | Paramètres / limites |
| --- | --- | --- |
| Texte acheté téléchargé et manifeste de bibliothèque | package Dart cryptography **2.9.0**, AesGcm.with256bits() | AES, mode GCM, clé 256 bits, nonce 96 bits, tag 128 bits |
| Génération de clé | newSecretKey du même package, une clé par lecteur sur l'appareil | génération aléatoire par bibliothèque; pas de dérivation d'un mot de passe |
| Conservation de clé | FlutterSecureStorage / iOS Keychain | secret conservé localement; aucun affichage/export de clé dans l'interface |
| Protection des fichiers | SecretBox contenant nonce, chiffré et tag | contrôle d'intégrité à la lecture; fichier illisible supprimé par le code |
| Réseau | connexions HTTPS API et SDK | TLS négocié par les composants; version/suite négociées non relevées, ne pas inventer de paramètres fixes |
| Jetons de compte | stockage sécurisé natif | mécanismes iOS; pas d'algorithme propriétaire Shelf |
| Nom de cache audio | SHA-256 via package crypto | empreinte pour nommage, pas chiffrement audio |

Les fichiers audio/médias sont dans le stockage privé de l'application, sans
couche AES appliquée par ce magasin de téléchargements. Base64 sert à encoder
les clés; ce n'est pas un algorithme de chiffrement. Les identifiants de compte et
les droits sont séparés par lecteur; les contenus payants sont contrôlés par le
serveur. Les copies hors ligne expirent après trente jours depuis la dernière
vérification réussie. Une révocation entraîne le retrait à la vérification suivante.
La suppression du stockage d'un lecteur supprime ses fichiers et sa clé locale.
Les clés de signature App Store Connect et le chiffrement de sauvegarde du serveur
ne sont pas la fonction de chiffrement distribuée par cette application.

Références techniques : mobile/lib/purchases/download_store.dart;
mobile/lib/purchases/library_controller.dart; mobile/lib/accounts/account_service.dart;
mobile/lib/audio/audio_cache_store.dart; mobile/pubspec.lock.
Cette annexe reprend l'inventaire vérifié; elle n'atteste pas une inspection binaire
exhaustive de chaque dépendance ni une analyse des suites TLS à l'exécution.

## Rationale de protection des contenus — à soumettre pour confirmation

La couche AES des livres/manifeste protège les contenus contre l'utilisation
non autorisée. Les utilisateurs ne peuvent ni choisir une clé ni chiffrer des
fichiers arbitraires. Cette fonction paraît pertinente pour l'annexe I, catégorie
6 du décret 2007-663. Nous demandons confirmation de son application au logiciel
complet, compte tenu des communications HTTPS, des jetons et du Keychain.
Aucune dispense officielle ni autorisation n'est revendiquée dans ce brouillon.

## Guide utilisateur et administrateur succinct

Lecteur : consulter le catalogue et l'extrait gratuit; utiliser son compte lecteur;
les droits vérifiés donnent accès aux livres de sa bibliothèque et, si permis,
aux téléchargements. Les fonctions d'achat Apple restent désactivées à ce stade.
Le lecteur ne configure pas la cryptographie et n'obtient pas la clé.
Administrateur : gérer le catalogue et les droits via le serveur; ne pas modifier
les historiques financiers. Aucun réglage utilisateur ne change l'algorithme AES.
Ce guide décrit les fonctions existantes, pas une autorisation de lancement.
