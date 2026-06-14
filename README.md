# BlogPlatform / Aperture

WordPress блог платформа со custom Blocksy child theme и workflow за автори, уреднички преглед, категории, реакции и frontend објавување на текстови.

## Screenshots

### Desktop

![Aperture desktop homepage](docs/screenshots/home-desktop.jpg)

### Mobile

![Aperture mobile homepage](docs/screenshots/home-mobile.jpg)

## Што содржи проектот

- `app/public/wp-content/themes/blocksy-child/` - custom child theme за Aperture UI.
- `app/public/wp-content/mu-plugins/aperture-workflow.php` - workflow логика за frontend submit, plugin bootstrap и editorial features.
- `app/public/wp-content/uploads/blocksy/css/global.css` - генериран Blocksy CSS потребен за тековниот visual style.
- `docs/screenshots/` - screenshots од тековниот изглед на сајтот.

## Главни функционалности

- Editorial homepage со hero, topics и featured content.
- Frontend страници за `Write`, `My content`, `Explore`, `Topics`, `About`, `Sign in`, `Register` и `Contributors`.
- Автоматско креирање/врзување на платформските страници преку child theme.
- Workflow за submit/draft на постови од frontend.
- Поддршка за PublishPress, WP User Frontend, WP ULike, Post Views Counter, Antispam Bee и role/capability management.

## Локално пуштање

Проектот е подготвен за Local WP.

1. Клонирај го репото во Local Sites директориум или отвори го постоечкиот Local WP site.
2. Во Local WP стартувај го сајтот `BlogPlatform`.
3. Локалниот домен од тековната инсталација е `http://aprture.issok/`.
4. Ако креираш нова околина, копирај `app/public/wp-config.example.php` во `app/public/wp-config.php` и внеси ги твоите локални DB вредности.
5. Импортирај база од приватен/local backup. Базата намерно не е дел од GitHub репото.
6. Инсталирај WordPress core, parent темата `Blocksy` и plugin зависностите наведени подолу.
7. Во WordPress активирај ја темата `Aperture - Premium Blog Platform Child` ако не е активна.

## Dependencies што не се commit-уваат

Овие работи се потребни во WordPress околината, но не се дел од Git репото затоа што се third-party/runtime код:

- WordPress core.
- Parent theme: `Blocksy`.
- Plugins: `PublishPress Planner`, `Antispam Bee`, `Blocksy Companion`, `WP User Frontend`, `Remove Dashboard Access`, `Post Views Counter`, `WP ULike`, `PublishPress Capabilities`.

## Што намерно не се commit-ува

Ова репо не ги верзионира локалните и чувствителни фајлови:

- WordPress core (`wp-admin`, `wp-includes`, root WordPress PHP files).
- Third-party parent themes и plugins.
- `app/public/wp-config.php` - содржи DB credentials и WordPress salts.
- `app/sql/` и `*.sql` - database dumps може да содржат корисници, лозинки, emails и приватна содржина.
- `app/public/wp-content/uploads/` - upload/media library е runtime content и лесно расте во големина.
- `logs/`, `conf/`, `app/.env*` - Local WP runtime, machine-specific конфигурации и логови.

Исклучок е `app/public/wp-content/uploads/blocksy/css/global.css`, затоа што Blocksy го генерира овој CSS и е потребен за тековниот изглед.

## GitHub remote

Remote репото:

```bash
git@github.com:AndrejT03/blogplatform.git
```

HTTPS алтернатива:

```bash
https://github.com/AndrejT03/blogplatform.git
```

## Забелешка

Пред deployment на жив сервер, постави нови WordPress salts, продукциски DB credentials и свеж database export/import процес. Реалната `wp-config.php` никогаш не треба да оди на GitHub.
