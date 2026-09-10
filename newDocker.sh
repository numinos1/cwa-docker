#!/bin/bash
#
# Builds the tarballs that setup_dev_environment.php pulls down to rebuild the Docker
# development environment. Each content directory is tarred only when its `ls -lR`
# listing differs from the cached copy in /tmp, so unchanged directories cost nothing.
#
# USAGE
#   ./newDocker.sh              -- no uploads tarball (default)
#   ./newDocker.sh uploads=y    -- also build uploads.tar.gz
#
# uploads is ~740 MB and the media library is append-mostly, so rebuilding and
# transferring it on every clone is almost always wasted time and disk. When it is
# skipped, setup_dev_environment.php creates an empty uploads directory instead --
# the site runs fine, images are just missing.

# --- Argument parsing -------------------------------------------------------------
MAKE_UPLOADS=0

for ARG in "$@"; do
    case "$(echo "$ARG" | tr '[:upper:]' '[:lower:]')" in
        uploads=y|uploads=yes|uploads=1|uploads=true)
            MAKE_UPLOADS=1
            ;;
        uploads=n|uploads=no|uploads=0|uploads=false)
            MAKE_UPLOADS=0
            ;;
        *)
            echo "WARNING: ignoring unrecognized argument '$ARG'." >&2
            echo "         Usage: ./newDocker.sh [uploads=y]" >&2
            ;;
    esac
done

echo "newDocker.sh started at $(date)"
if [ "$MAKE_UPLOADS" -eq 1 ]; then
    echo "uploads: ENABLED (uploads.tar.gz will be built if the directory has changed)"
else
    echo "uploads: skipped (pass uploads=y to include it)"
fi

echo "Starting mysql backup"
mysqldump -u cwacwops_wp540 cwacwops_wp540 --no-tablespaces | gzip > backup.sql.gz
echo "mysql backup written"

echo "Testing jetpack-waf";
ls -lR --full-time /home/cwacwops/www/wp-content/jetpack-waf/ > /tmp/directory_listing_jetpack-waf_new.txt
diff -q /tmp/directory_listing_jetpack-waf_old.txt /tmp/directory_listing_jetpack-waf_new.txt
if [ $? -eq 0 ]; then
    echo "No changes detected"
else
    echo "CHANGES DETECTED!"
    tar -czf jetpack-waf.tar.gz www/wp-content/jetpack-waf
    echo "jetpack-waf compressed. Copying listing"
    cp /tmp/directory_listing_jetpack-waf_new.txt /tmp/directory_listing_jetpack-waf_old.txt
fi

echo "Testing languages";
ls -lR --full-time /home/cwacwops/www/wp-content/languages/ > /tmp/directory_listing_languages_new.txt
diff -q /tmp/directory_listing_languages_old.txt /tmp/directory_listing_languages_new.txt
if [ $? -eq 0 ]; then
    echo "No changes detected"
else
    echo "CHANGES DETECTED!"
    tar -czf languages.tar.gz www/wp-content/languages
    echo "languages compressed. Copying listing"
    cp /tmp/directory_listing_languages_new.txt /tmp/directory_listing_languages_old.txt
fi


echo "Testing plugins";
ls -lR --full-time /home/cwacwops/www/wp-content/plugins/ > /tmp/directory_listing_plugins_new.txt
diff -q /tmp/directory_listing_plugins_old.txt /tmp/directory_listing_plugins_new.txt
if [ $? -eq 0 ]; then
    echo "No changes detected"
else
    echo "CHANGES DETECTED!"
    tar -czf plugins.tar.gz www/wp-content/plugins
    echo "plugins compressed. Copying listing"
    cp /tmp/directory_listing_plugins_new.txt /tmp/directory_listing_plugins_old.txt
fi

echo "Testing themes";
ls -lR --full-time /home/cwacwops/www/wp-content/themes/ > /tmp/directory_listing_themes_new.txt
diff -q /tmp/directory_listing_themes_old.txt /tmp/directory_listing_themes_new.txt
if [ $? -eq 0 ]; then
    echo "No changes detected"
else
    echo "CHANGES DETECTED!"
    tar -czf themes.tar.gz www/wp-content/themes
    echo "themese compressed. Copying listing"
    cp /tmp/directory_listing_themes_new.txt /tmp/directory_listing_themes_old.txt
fi

echo "Testing updraft";
ls -lR --full-time /home/cwacwops/www/wp-content/updraft/ > /tmp/directory_listing_updraft_new.txt
diff -q /tmp/directory_listing_updraft_old.txt /tmp/directory_listing_updraft_new.txt
if [ $? -eq 0 ]; then
    echo "No changes detected"
else
    echo "CHANGES DETECTED!"
    tar -czf updraft.tar.gz --exclude='www/wp-content/updraft/backup_*' www/wp-content/updraft
    echo "updraft compressed. Copying listing"
    cp /tmp/directory_listing_updraft_new.txt /tmp/directory_listing_updraft_old.txt
fi

echo "Testing upgrade-temp-backup";
ls -lR --full-time /home/cwacwops/www/wp-content/upgrade-temp-backup/ > /tmp/directory_listing_upgrade-temp-backup_new.txt
diff -q /tmp/directory_listing_upgrade-temp-backup_old.txt /tmp/directory_listing_upgrade-temp-backup_new.txt
if [ $? -eq 0 ]; then
    echo "No changes detected"
else
    echo "CHANGES DETECTED!"
    tar -czf upgrade-temp-backup.tar.gz www/wp-content/upgrade-temp-backup
    echo "upgrade-temp-backup compressed. Copying listing"
    cp /tmp/directory_listing_upgrade-temp-backup_new.txt /tmp/directory_listing_upgrade-temp-backup_old.txt
fi

echo "Testing upgrade";
ls -lR --full-time /home/cwacwops/www/wp-content/upgrade/ > /tmp/directory_listing_upgrade_new.txt
diff -q /tmp/directory_listing_upgrade_old.txt /tmp/directory_listing_upgrade_new.txt
if [ $? -eq 0 ]; then
    echo "No changes detected"
else
    echo "CHANGES DETECTED!"
    tar -czf upgrade.tar.gz www/wp-content/upgrade
    echo "upgrade compressed. Copying listing"
    cp /tmp/directory_listing_upgrade_new.txt /tmp/directory_listing_upgrade_old.txt
fi

if [ "$MAKE_UPLOADS" -eq 1 ]; then
    echo "Testing uploads";
    ls -lR --full-time /home/cwacwops/www/wp-content/uploads/ > /tmp/directory_listing_uploads_new.txt
    diff -q /tmp/directory_listing_uploads_old.txt /tmp/directory_listing_uploads_new.txt
    if [ $? -eq 0 ]; then
        echo "No changes detected"
    else
        echo "CHANGES DETECTED!"
        tar -czf uploads.tar.gz www/wp-content/uploads
        echo "uploads compressed. Copying listing"
        cp /tmp/directory_listing_uploads_new.txt /tmp/directory_listing_uploads_old.txt
    fi
else
    # Skipping the block entirely also skips the `ls -lR` over ~3,500 files, which is
    # the slowest part of this script.
    #
    # Remove any tarball left by an earlier uploads=y run. Without this, the download
    # side would find a stale archive and quietly ship weeks-old media -- which is
    # worse than no media, because it looks current. The cached listing in /tmp is
    # deliberately NOT touched, so the next uploads=y run still sees every change that
    # has accumulated since the last one.
    if [ -f uploads.tar.gz ]; then
        echo "Removing stale uploads.tar.gz from a previous uploads=y run"
        rm -f uploads.tar.gz
    fi
    echo "Skipping uploads (pass uploads=y to build it)"
fi
echo "Script finished at $(date)"

