# Demande de confirmation — protection des livres téléchargés Shelf

**BROUILLON — NON ENVOYÉ.** Destinataire officiel : controle@ssi.gouv.fr
(contact d'aide affiché sur le portail ANSSI). Pas un dépôt de déclaration.
Pièce proposée : SHELF_TECHNICAL_DRAFT.md, après validation et copie privée finale.

Objet : Shelf iOS — application de l'annexe I catégorie 6 du décret 2007-663

Madame, Monsieur,

Je prépare la distribution en France de Shelf - شیلف, une librairie numérique et
un lecteur de livres en pachto/farsi, iOS 1.0.0 (4), services.shelf.app. Le lancement
public est suspendu et la fonction d'achat Apple est actuellement désactivée.

Le logiciel utilise AES-256-GCM (bibliothèque Dart cryptography) pour les textes
achetés téléchargés et le manifeste de bibliothèque, avec une clé locale par
lecteur stockée dans le Keychain iOS. Cette fonction soutient le contrôle d'accès
aux contenus éditoriaux. Il n'existe pas d'interface permettant de choisir une clé,
d'en exporter une ou de chiffrer des fichiers arbitraires. L'application utilise
aussi HTTPS pour les échanges réseau et le stockage sécurisé iOS pour les jetons.
Vous trouverez la description des fonctions et limites en annexe technique.

Pourriez-vous confirmer si cette application entre dans la dispense de formalités
préalables de l'annexe I, catégorie 6, du décret 2007-663 pour la protection contre
la copie ou l'utilisation illicite, ou si une déclaration est nécessaire compte
tenu des autres fonctions décrites ? Si elle est dispensée, pouvez-vous fournir
une réponse écrite précisant le périmètre de la dispense ? Apple demande, pour des
algorithmes standard non fournis par son OS et une distribution en France, une
« French encryption declaration »; quel document pouvez-vous délivrer dans notre cas ?

Si une déclaration est nécessaire, merci de confirmer la procédure applicable au
fournisseur individuel et les pièces attendues, et si l'attestation de déclaration
émise après acceptation est le document justificatif à communiquer à Apple.
Nous ne sollicitons pas une autorisation d'exportation depuis la France ni une
certification de qualité du logiciel.

Cordialement,
Ajmal Aand
[Courriel propriétaire déjà connu, à reprendre dans la copie privée]
[Adresse/pays et téléphone à compléter privément]
