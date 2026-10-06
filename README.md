# Cyonima LMS — Component Joomla 5

`com_cyonima` est un **Learning Management System** natif pour Joomla 5, pensé pour se rapprocher d'un Udemy ou d'un LinkedIn Learning : des **cours** multimédia publiés par des **teachers**, suivis par des **students**, avec devoirs notés, examens formels, certificats personnalisés et learning paths.

L'administration des **comptes utilisateurs reste déléguée à Joomla** : le composant crée simplement deux groupes utilisateurs (`Teacher` et `Student`) et s'appuie sur l'ACL native de Joomla.

---

## Sommaire

- [Fonctionnalités](#fonctionnalités)
- [Prérequis](#prérequis)
- [Installation](#installation)
- [Rôles et permissions](#rôles-et-permissions)
- [Configuration](#configuration)
- [Utilisation côté teacher](#utilisation-côté-teacher)
- [Utilisation côté student](#utilisation-côté-student)
- [Certificats personnalisés](#certificats-personnalisés)
- [Architecture](#architecture)
- [Modèle de données](#modèle-de-données)
- [Développement](#développement)

---

## Fonctionnalités

- **Cours** avec description riche, image, publication, accès et langue.
- **Leçons** de 6 types : contenu riche (`content`), vidéo (`video`), PDF (`pdf`), lien externe (`link`), devoir (`assignment`) et examen (`exam`).
- **Devoirs et exercices notés** : dépôt de texte + fichier, notation (score + feedback) par le teacher.
- **Examens formels** : QCM (choix unique, multiple, vrai/faux), barème, note de passage, temps limite, nombre de tentatives, mélange des questions.
- **Suivi / monitoring** : nombre d'élèves inscrits, progression (%), résultats aux devoirs et examens, par cours.
- **Inscription** en self-enrollment.
- **Certificats de suivi** générés automatiquement à la validation d'une formation, téléchargeables en PNG.
- **Templates de certificat** personnalisables par teacher (image JPEG/PNG + positions des textes).
- **Learning paths** : regroupement ordonné de plusieurs cours.

---

## Prérequis

| Élément      | Exigence                                   |
|--------------|--------------------------------------------|
| Joomla       | 5.x                                        |
| PHP          | 8.2+                                       |
| Extensions   | `gd` (génération des certificats)          |
| Base         | MySQL 8 / MariaDB 10.4+                    |

> La bibliothèque GD est **requise** pour générer les certificats. Sans elle, l'installation fonctionne mais la génération du certificat échouera.

---

## Installation

1. Compressez le dossier `com_cyonima/` en un fichier `.zip` (le manifeste `cyonima.xml` doit être à la racine du zip).
2. Dans Joomla : **Système → Installer → Extensions → Upload Package File**, choisissez le zip.
3. L'installation exécute :
   - la création des 13 tables (préfixe `#__cyonima_*`) ;
   - la création des groupes utilisateurs `Teacher` et `Student` (enfants du groupe `Registered`) ;
   - l'application de l'ACL par défaut (voir ci-dessous).

> L'installation ne supprime **jamais** un groupe existant portant le même titre. À la désinstallation, les groupes `Teacher` et `Student` sont retirés.

---

## Environnement de test (Docker)

Un environnement reproductible est fourni dans `docker/` (Joomla 5 + MariaDB).

```bash
# 1. Une seule fois : autoriser Docker pour votre utilisateur (puis re-login)
sudo usermod -aG docker $USER

# 2. Démarrer l'environnement + installer Joomla + installer com_cyonima
./docker/up.sh
```

Résultat :

| Élément    | Valeur                                        |
|------------|-----------------------------------------------|
| Frontend   | http://localhost:8080/                         |
| Admin      | http://localhost:8080/administrator            |
| Admin user | `admin`                                        |
| Password   | `Cyonima2026!`                                 |

Commandes utiles :

```bash
docker compose -f docker/docker-compose.yml logs -f joomla   # logs
docker compose -f docker/docker-compose.yml down             # arrêter
docker compose -f docker/docker-compose.yml down -v          # tout réinitialiser
```

Fichiers :

- `docker/docker-compose.yml` — services `joomla` + `mariadb`, source montée en `/cyonima`.
- `docker/up.sh` — orchestration complète (démarrage, install Joomla via CLI, install du composant).
- `docker/install-component.php` — installe `com_cyonima` depuis le dossier monté et vérifie (tables, groupes).
- `docker/cleanup.php` — réinitialise l'état (tables, extension, asset, menus, fichiers) avant réinstallation.

> Note : le script `up.sh` installe Joomla via son CLI (`installation/joomla.php install`) car l'auto-installation du conteneur officiel n'est pas déclenchée. L'installation du composant neutralise temporairement les plugins d'extension (`finder`, `joomla`, `joomlaupdate`) non autochargeables en CLI, puis les restaure.

---

## Rôles et permissions

Deux groupes utilisateurs sont créés et délégués à l'administration des comptes Joomla :

| Groupe   | Parent      | Rôle                                                |
|----------|-------------|-----------------------------------------------------|
| `Teacher`| `Registered`| Publie et gère les cours, devoirs, examens, certificats. |
| `Student`| `Registered`| Suit les cours, dépose des devoirs, passe des examens. |

### ACL par défaut appliquée à l'installation

- **Teacher** : `core.manage`, `core.create`, `core.edit`, `core.edit.state`, `core.delete`, `course.teach`, `grade.submission`.
- **Student** (et tout utilisateur connecté) : `course.enroll`.

Actions personnalisées définies dans `access.xml` :

- `course.enroll` — s'inscrire à un cours ;
- `course.teach` — enseigner (publier des cours) ;
- `grade.submission` — noter les devoirs.

### Accès à l'administration pour les teachers

Par défaut, `Teacher` est un groupe **frontend** (enfant de `Registered`). Pour qu'un teacher gère ses cours via l'administration Joomla, accordez-lui l'accès backend :

1. **Système → Global Configuration → Permissions**, ou créez un niveau d'accès dédié ;
2. donnez au groupe `Teacher` la permission **Backend Login** (`core.login.admin`), **ou** placez le groupe `Teacher` sous un groupe disposant déjà de cet accès.

Les permissions du composant (`core.manage`, etc.) lui sont déjà attribuées : il ne reste que l'accès backend à activer.

---

## Configuration

**Composants → Cyonima LMS → Options** (ou `Système → Global Configuration → Cyonima LMS`) :

| Option                          | Défaut                          | Description                                            |
|---------------------------------|---------------------------------|--------------------------------------------------------|
| Certificate folder              | `images/com_cyonima/certificates` | Dossier relatif de stockage des certificats générés.  |
| Media folder                    | `images/com_cyonima`            | Dossier racine des médias (templates, fichiers de devoirs). |
| Certificate number prefix       | `CYN`                           | Préfixe des numéros de certificat.                     |
| Notify teacher on submission    | Non                             | Notifie le teacher à chaque dépôt de devoir.           |

---

## Utilisation côté teacher

1. **Créer un cours** : Composants → Cyonima LMS → Courses → New (titre, description, image, statut).
2. **Ajouter des leçons** : dans la liste des cours, lien « Lessons », ou menu Lessons. Choisir le type de leçon :
   - `content` : contenu HTML ;
   - `video` : fichier vidéo (MP4) ou URL d'iframe (YouTube, Vimeo…) ;
   - `pdf` : chemin du fichier PDF ;
   - `link` : lien externe ;
   - `assignment` : à relier à un devoir (menu Assignments) ;
   - `exam` : à relier à un examen (menu Exams).
3. **Devoirs** : Composants → Assignments (description, date limite, barème). La notation se fait dans le menu **Submissions** (score + feedback).
4. **Examens** : Composants → Exams (note de passage, temps, tentatives), puis ajouter des **Questions** (single / multiple / truefalse).
5. **Monitoring** : depuis la liste des cours, lien « Monitor » → élèves inscrits, progression, résultats.
6. **Learning paths** : Composants → Learning Paths (regrouper des cours dans un ordre).
7. **Certificat** : Composants → Certificate Templates, uploader une image JPEG/PNG et définir les positions des textes (voir section dédiée).

---

## Utilisation côté student

- **S'inscrire** à un cours depuis le catalogue (bouton « Enroll »).
- **Suivre** les leçons et marquer la complétion (« Mark as complete »).
- **Déposer** un devoir (texte et/ou fichier).
- **Passer** un examen (QCM noté).
- **Consulter** sa progression dans « My courses ».
- **Télécharger** son certificat une fois la formation validée (100 % des leçons complétées), depuis « My certificates ».

La validation d'une formation déclenche automatiquement l'émission du certificat.

---

## Certificats personnalisés

Chaque teacher peut fournir une image (JPEG/PNG) servant de modèle. Les textes sont superposés selon une configuration JSON stockée dans le champ **Parameters** du template :

```json
{
  "name":   { "x": 0, "y": 400, "size": 48, "color": "#000000", "align": "center" },
  "course": { "x": 0, "y": 500, "size": 28, "color": "#333333", "align": "center" },
  "date":   { "x": 0, "y": 560, "size": 22, "color": "#666666", "align": "center" },
  "number": { "x": 0, "y": 120, "size": 18, "color": "#999999", "align": "right" }
}
```

Placeholders disponibles : `name` (nom de l'élève), `course` (titre du cours), `date` (date d'émission), `number` (numéro de certificat).

- `align` : `left`, `center` ou `right` (relatif à la largeur de l'image).
- `size` : taille de police (px).
- `color` : couleur hexadécimale.
- `x` / `y` : position ; avec `align="center"` ou `right`, `x` sert d'offset.

Le certificat est généré en PNG par la bibliothèque GD.

---

## Architecture

Composant MVC moderne Joomla 5 (namespaces PSR-4 `Cyonima\Component\Cyonima`), avec injection de dépendances.

```
com_cyonima/
├── cyonima.xml                      # Manifeste d'installation
├── script.php                       # Script d'installation (groupes + ACL)
├── admin/
│   ├── access.xml                   # Actions ACL
│   ├── config.xml                   # Options du composant
│   ├── services/provider.php        # Provider DI (MVCFactory, dispatcher)
│   ├── sql/                         # install / uninstall SQL
│   ├── src/
│   │   ├── Controller/              # Contrôleurs admin (list + edit)
│   │   ├── Model/                   # Modèles admin
│   │   ├── Table/                   # Classes JTable
│   │   ├── View/                    # Vues admin
│   │   └── Helper/                  # CertificateGenerator, CertificateHelper, ProgressHelper…
│   ├── tmpl/                        # Templates admin (layouts)
│   └── language/en-GB/              # Chaînes de langue
├── site/
│   ├── services/provider.php        # Provider DI (MVCFactory, dispatcher, router)
│   ├── src/
│   │   ├── Controller/              # Contrôleurs frontend (enroll, submit, exam…)
│   │   ├── Model/                   # Modèles frontend
│   │   ├── View/                    # Vues frontend
│   │   ├── Service/Router.php       # Routage SEF (RouterView)
│   │   └── Helper/
│   ├── tmpl/                        # Templates frontend
│   └── language/en-GB/
└── media/                           # css / js / images (publié dans /media/com_cyonima)
```

---

## Modèle de données

13 tables (préfixe `#__` = préfixe de base Joomla) :

| Table                                   | Rôle                                                        |
|-----------------------------------------|-------------------------------------------------------------|
| `#__cyonima_courses`                    | Cours (titre, description, image, publication, teacher…)    |
| `#__cyonima_lessons`                    | Leçons d'un cours (type, contenu, url, media, durée…)       |
| `#__cyonima_enrollments`                | Inscriptions d'un student à un cours (statut, progression)  |
| `#__cyonima_lesson_progress`            | Progression par leçon (complété, score)                     |
| `#__cyonima_assignments`                | Devoirs / exercices notés                                   |
| `#__cyonima_submissions`                | Copies rendues (contenu, fichier, score, feedback)          |
| `#__cyonima_exams`                      | Examens (note de passage, temps, tentatives, mélange)       |
| `#__cyonima_questions`                  | Questions d'examen (type, options JSON, réponse, points)    |
| `#__cyonima_exam_attempts`              | Tentatives d'examen (score, réponses, réussi)               |
| `#__cyonima_certificate_templates`      | Modèles de certificat (image + positions JSON)              |
| `#__cyonima_certificates`               | Certificats émis (numéro, date, chemin fichier)             |
| `#__cyonima_learning_paths`             | Parcours de formation (learning paths)                      |
| `#__cyonima_learning_path_courses`      | Association ordonnée cours ↔ learning path                  |

---

## Développement

### Conventions

- **Namespace** : `Cyonima\Component\Cyonima\{Administrator|Site}\…`.
- Les noms de classes multi-mots suivent la convention `ucfirst` de Joomla (ex. `LearningpathModel`, pas `LearningPathModel`), car `MVCFactory` ne capitalise que la première lettre du nom de vue.
- Les `Table` vivent dans `Administrator\Table` et sont résolues côté site via le fallback `Administrator` de `MVCFactory::createTable()`.
- L'édition admin utilise des formulaires manuels (`name="jform[field]"`), avec `save`/`apply`/`cancel` sur les contrôleurs singuliers et les actions de masse sur les contrôleurs pluriels.

### Vérifications

```bash
# Lint de tous les fichiers PHP
find com_cyonima -name '*.php' -exec php -l {} \;

# Validation des manifestes XML
php -r 'foreach (["com_cyonima/cyonima.xml","com_cyonima/admin/access.xml","com_cyonima/admin/config.xml"] as $f) { echo $f, " => ", (simplexml_load_file($f) ? "OK" : "FAIL"), "\n"; }'
```

### Points d'extension possibles

- Notifications email (déjà prévu via l'option `notify_teacher_on_submission`).
- Intégration `com_categories` pour catégoriser les cours.
- Versioning / historique (l'interface `VersionableTableInterface` est déjà déclarée sur `CourseTable`).
- Traductions supplémentaires (seul `en-GB` est fourni).

---

## Licence

GNU General Public License version 2 ou ultérieure — voir la notice dans chaque fichier source.
