# CLAUDE.md — CWA Docker Development Environment

This repo builds a **local Docker clone of the CW Academy production site** so snippet and
database work can be done safely. It is the companion to
[`cwacwops_newdatabase`](https://github.com/rolandksmith/cwacwops_newdatabase), which holds
the PHP snippets themselves.

**Keep the two straight:**

| Repo | Holds |
|---|---|
| `cwacwops_newdatabase` | the snippets, DAL classes, planning docs, deploy tooling |
| **this repo** | everything that builds and runs the Docker dev environment |

Anything that exists to stand the environment up belongs here — including `newDocker.sh`,
which runs on the **production server** but exists solely to feed this environment.

---

## The workflow

From `~/cwa-docker`:

```bash
ssh cwa
./newDocker.sh              # on production: build the tarballs + DB dump
exit
php setup_dev_environment.php   # locally: download, extract, start Docker, configure
```

That is the whole loop. What each step does:

### 1 · `newDocker.sh` — runs on production, at `/home/cwacwops/`

Dumps the database and tars each `wp-content` directory, writing all of it to the
production home directory. **Each directory is only re-tarred when it has actually
changed** — the script keeps an `ls -lR` listing per directory in `/tmp` and diffs against
it, so an unchanged directory costs nothing.

```bash
./newDocker.sh              # no uploads tarball  (default)
./newDocker.sh uploads=y    # also build uploads.tar.gz
```

**`uploads` is opt-in.** It is ~740 MB, the media library is append-mostly, and the
`ls -lR` over ~3,500 files is the slowest part of the run. Development work is on snippets
and the database; media is scenery. When it is skipped, `setup_dev_environment.php` creates
an empty uploads directory and the site runs normally, just without images.

Skipping also **deletes any stale `uploads.tar.gz`** from an earlier `uploads=y` run —
otherwise the download side would find an old archive and quietly ship weeks-old media,
which is worse than none because it looks current. The cached `/tmp` listing is
deliberately **not** touched, so the next `uploads=y` run still sees everything that
changed meanwhile.

⚠️ **This file must be copied to the server by hand** — the repo copy is the source of
truth, but nothing deploys it. Keep the executable bit.

### 2 · `setup_dev_environment.php` — runs locally

The entry point. In order:

| Step | What |
|---|---|
| 1 | runs `startup.sh` |
| **1b** | **guarantees `www/wp-content/uploads` exists** — see below |
| 2 | reads `.env` |
| 3–4 | waits for MySQL, connects |
| 6 | makes `K7OJL` an administrator, and deactivates the *JAVASCRIPT Refresh Requests* snippet |

**Step 1b** handles three states without needing to know which happened: `startup.sh`
already extracted uploads (do nothing); a tarball is on the server but was not fetched
(scp and extract); no tarball at all (create an empty directory). **It never fails the
setup** — the worst case warns and falls back to the empty directory.

The callsign is currently hardcoded to `K7OJL`; `getUserCallsign()` still exists and is
commented out of the run.

### 3 · `startup.sh` — the downloader

`scp`s each tarball from the `cwa` ssh alias, extracts into `./www/wp-content/`, pulls
`backup.sql.gz` into `init/`, then `docker-compose up -d` and runs `prep.sh` in the
container.

⚠️ It still attempts an unconditional `scp cwa:uploads.tar.gz` (lines 41–44). With no
tarball present that fails harmlessly and Step 1b covers it, but it prints a confusing
`scp: No such file or directory` followed by a `tar` error. **Removing those four lines
would silence it** — left in place for now so the two sides stay independent.

### 4 · Inside the containers

- **`init/migrate.sh`** — runs at database init. Rewrites production URLs to the dev URL
  across `options`, `posts`, `postmeta` **and `snippets`**. Driven by `$production_url`,
  `$prod_admin_url` and `$dev_url` from `.env`.
- **`init/prep.sh`** — runs in the WordPress container. Disables the plugins named in
  `$wp_plugins_to_disable` by renaming their folders, and fixes `wp-content` ownership.

**Edit the copies under `init/`** — those are what `docker-compose.yml` mounts. The
top-level `prep.sh` and `migrate.sh` are stale duplicates and are gitignored.

---

## Ports and access

| | |
|---|---|
| WordPress | **http://localhost:3073** |
| phpMyAdmin | **http://localhost:8080** |
| MySQL from the host | **127.0.0.1:3074** |
| MySQL from the wordpress container | `db:3306` |

Container names are `wordpress`, `mysql` and `phpmyadmin`, so the database is reachable
directly with:

```bash
docker exec -i mysql mysql -u <db_user> -p<db_password> <db_name> -e "SQL HERE;"
```

`WP_HOME` and `WP_SITEURL` are forced to `http://localhost:3073` in
`docker-compose.yml`, and `WP_DEBUG` / `WP_DEBUG_LOG` are on.

---

## Other scripts

| Script | Purpose |
|---|---|
| `takedown.sh` | `rm -rf mysql www` and removes `init/backup.sql.gz` — a full reset before rebuilding |
| `startupFromBackup.sh` | Rebuilds from a **dated backup** in `cwa:backups/backups-YYYY-MM-DD/` instead of a fresh dump. **This is the forensic path** — the one to use to find out what a record looked like at some point in the past |
| `startup7am.sh` | Older variant using `backup7am.sql.gz` / `wp-content7am.tar.gz` |
| `sqlFileSplit.php` | Splits a very large dump into `sql_split/` |

### ⚠️ Two things to know about `startupFromBackup.sh`

1. **The date is hardcoded** in the script (currently `2025-11-15`). Edit it before use.
2. **It uses `--strip-components=2`**, because the backup tarballs are made with absolute
   paths (`home/cwacwops/www/wp-content/...`) while `newDocker.sh`'s are relative
   (`www/wp-content/...`). The two sets are not interchangeable.
3. **Production backup retention is now 90 days**, and only the **newest three** carry
   `uploads.tar.gz` — see `daily_backup.sh` in the snippet repo. Older dates simply will
   not be there.

---

## Conventions

- **Commit straight to `main`.** This repo has no PR workflow; the snippet repo does.
- ⚠️ **Pull before you push.** This repo has more than one contributor, and `main` has
  been pushed to from elsewhere. A merge is normal here; **never force-push.**
- **Images are pinned to match production** — `wordpress:7.1-php8.4-apache` and
  `mysql:8.4.11` (moved up from `mysql:8.0` on 2026-09-16). PHP 8.4 matters: it is what
  production serves, so snippet behavior in Docker matches live. An upstream commit once
  reverted these to `6.1.1-php7.4` and `mysql:5.7`; if that reappears, it is a regression.
  ⚠️ MySQL 8.4 upgrades the `./mysql` data directory in place, and 8.0 cannot open it
  afterward — going back means rebuilding the database from a dump.
- **`.env` is gitignored** and holds the database credentials, table prefix, the plugin
  disable list, and the production/dev URLs used by `migrate.sh`.
- **Never commit dumps or archives.** `.gitignore` covers `*.gz`, `*.tar`, `www`, `mysql`,
  `backup.sql`, `sql_split/`, `cwa-docker-hold/` and `home/`. A working checkout carries
  roughly **40 GB** of those; none of it belongs in git.
- The database arrives **cloned from production**, which means it contains **live SES
  credentials and real member email addresses**. The CWA mail functions send **only** when
  `CWA_ENV === 'production'`, and this compose file declares `define( 'CWA_ENV', 'dev' );`
  in `WORDPRESS_CONFIG_EXTRA` — so Docker sends nothing. See the snippet repo's `CLAUDE.md`.
  **Do not remove that line, and do not set it to `'production'`.** An environment that
  declares nothing also sends nothing, so the failure mode is safe; it was not always
  — the guard used to key on `/.dockerenv` and sent real mail from anywhere else.
  Do not run CWA code against this database from a host PHP CLI either: no `wp-config.php`
  is loaded there, so nothing about this environment is declared at all.
