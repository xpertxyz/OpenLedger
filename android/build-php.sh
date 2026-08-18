#!/bin/bash
# Cross-compile PHP CLI for Android arm64 and drop it into the app as `libphp.so`.
#
# Why that filename: since Android 10 the system refuses to execute anything from an app's
# writable data directory (W^X). The one directory that stays executable is the extracted
# native library dir, and the packager only extracts files matching lib*.so — so the PHP
# binary has to be *named* like a shared library even though it is an executable. This is the
# standard workaround, not a hack around app signing.
#
#   ./android/build-php.sh            # arm64 only (covers essentially every current device)
#
# Extensions are the minimum this app actually uses. No openssl: the Android build has Google
# sign-in switched off, so nothing verifies an ID token, and dropping it removes the need to
# cross-build OpenSSL first. mbregex is off for the same reason — it needs oniguruma, and the
# app only ever calls mb_strlen/mb_substr, never mb_ereg.
set -euo pipefail

PHP_VERSION=${PHP_VERSION:-8.3.14}
ABI=${ABI:-arm64-v8a}
API=${API:-24}
NDK=${NDK:-$HOME/Library/Android/sdk/ndk/27.0.12077973}
HERE="$(cd "$(dirname "$0")" && pwd)"
WORK="$HERE/.php-build"
OUT="$HERE/app/src/main/jniLibs/$ABI"

case "$ABI" in
  arm64-v8a)   HOST=aarch64-linux-android ;;
  armeabi-v7a) HOST=armv7a-linux-androideabi ;;
  x86_64)      HOST=x86_64-linux-android ;;
  *) echo "unknown ABI $ABI"; exit 1 ;;
esac

[ -d "$NDK" ] || { echo "NDK not found at $NDK — set NDK=..."; exit 1; }

TC="$NDK/toolchains/llvm/prebuilt/darwin-x86_64/bin"
[ -d "$TC" ] || TC="$NDK/toolchains/llvm/prebuilt/linux-x86_64/bin"
export CC="$TC/${HOST}${API}-clang"
export CXX="$TC/${HOST}${API}-clang++"
export AR="$TC/llvm-ar" RANLIB="$TC/llvm-ranlib" STRIP="$TC/llvm-strip"
[ -x "$CC" ] || { echo "no compiler at $CC"; exit 1; }

mkdir -p "$WORK" && cd "$WORK"
SYSROOT="$WORK/sysroot"

# ── SQLite first. PHP has not bundled a copy since 7.4, so --with-pdo-sqlite needs a real
# libsqlite3 built for the target or the build dies late with "sqlite3.h file not found".
# The amalgamation is one C file and cross-compiles without incident.
SQLITE_VERSION=${SQLITE_VERSION:-3450200}          # 3.45.2
if [ ! -f "$SYSROOT/lib/libsqlite3.a" ]; then
  [ -f "sqlite-autoconf-$SQLITE_VERSION.tar.gz" ] || curl -fsSLo "sqlite-autoconf-$SQLITE_VERSION.tar.gz" \
    "https://www.sqlite.org/2024/sqlite-autoconf-$SQLITE_VERSION.tar.gz"
  [ -d "sqlite-autoconf-$SQLITE_VERSION" ] || tar xzf "sqlite-autoconf-$SQLITE_VERSION.tar.gz"
  ( cd "sqlite-autoconf-$SQLITE_VERSION"
    ./configure --host="$HOST" --prefix="$SYSROOT" --disable-shared --enable-static \
                --disable-readline CFLAGS="-Os -fPIC"
    make -j"$(sysctl -n hw.ncpu 2>/dev/null || nproc)" && make install )
fi
export PKG_CONFIG_PATH="$SYSROOT/lib/pkgconfig"
export PKG_CONFIG_LIBDIR="$SYSROOT/lib/pkgconfig"

[ -f "php-$PHP_VERSION.tar.gz" ] || curl -fsSLo "php-$PHP_VERSION.tar.gz" \
  "https://www.php.net/distributions/php-$PHP_VERSION.tar.gz"
[ -d "php-$PHP_VERSION" ] || tar xzf "php-$PHP_VERSION.tar.gz"
cd "php-$PHP_VERSION"

# Cross-compiling means configure cannot run its test binaries on the host, so the answers it
# would have measured are supplied here. Without these it guesses wrong and the build fails
# late, during make, with errors that look unrelated.
export CFLAGS="-Os -fPIE -fPIC -I$SYSROOT/include"
# max-page-size=16384 is not optional and not cosmetic. Android 15 introduced devices with a
# 16 KB memory page, and from November 2025 Play rejects an app targeting Android 15+ whose
# native libraries have LOAD segments aligned to anything smaller. The linker defaults to 4 KB
# here — the NDK's own 16 KB default applies to shared libraries, and this is linked -pie as an
# executable (see the libphp.so naming story above), so it does not inherit it. Getting this
# wrong fails at upload with "not compatible with 16 KB devices", never at build time.
export LDFLAGS="-pie -Wl,-z,max-page-size=16384 -L$SYSROOT/lib"
export SQLITE_CFLAGS="-I$SYSROOT/include"
export SQLITE_LIBS="-L$SYSROOT/lib -lsqlite3"
cat > config.cache <<'EOF'
ac_cv_func_pread=yes
ac_cv_func_pwrite=yes
ac_cv_func_fnmatch_works=yes
ac_cv_func_memcmp_working=yes
ac_cv_func_strtod=yes
ac_cv_c_bigendian_php=no
php_cv_lib_cost=no
# Android's libc (bionic) deliberately omits the BIND resolver API. configure cannot run its
# test binaries when cross-compiling, so it assumes these exist and ext/standard/dns.c then
# fails to compile against an incomplete struct __res_state. Saying no here compiles the
# DNS helpers out, which costs this app nothing: it resolves no hostnames.
ac_cv_func_res_nsearch=no
ac_cv_func_res_ndestroy=no
ac_cv_func_res_search=no
ac_cv_func_res_ninit=no
ac_cv_func_dn_expand=no
ac_cv_func_dn_skipname=no
ac_cv_func_dns_search=no
EOF

./configure --cache-file=config.cache \
  --host="$HOST" --target="$HOST" \
  --disable-all --disable-shared --enable-static=no \
  --enable-cli --disable-cgi --disable-phpdbg \
  --enable-session --enable-filter --enable-ctype --enable-tokenizer \
  --enable-mbstring --disable-mbregex \
  --enable-pdo --with-pdo-sqlite --with-sqlite3 \
  --without-pear --without-iconv --disable-opcache \
  --with-config-file-path=/system/etc

# Android's libc has no BIND resolver, and configure cannot find that out by running a test
# binary when it is cross-compiling — worse, PHP's own PHP_CHECK_FUNC macro `unset`s these
# cache variables before checking, so config.cache above cannot answer for them either. The
# generated header is the only place left to say no.
#
# ext/standard/php_dns.h derives HAVE_DNS_SEARCH_FUNC from these; with all of them undefined
# the entire DNS section of dns.c compiles out and the matching arginfo drops those functions
# to match. This app resolves no hostnames, so nothing is lost.
sed -i.bak \
  -e 's|^#define HAVE_RES_NSEARCH 1|/* #undef HAVE_RES_NSEARCH */|' \
  -e 's|^#define HAVE_RES_SEARCH 1|/* #undef HAVE_RES_SEARCH */|' \
  -e 's|^#define HAVE_DNS_SEARCH 1|/* #undef HAVE_DNS_SEARCH */|' \
  -e 's|^#define HAVE_DN_EXPAND 1|/* #undef HAVE_DN_EXPAND */|' \
  -e 's|^#define HAVE_DN_SKIPNAME 1|/* #undef HAVE_DN_SKIPNAME */|' \
  main/php_config.h

# bionic has no getdtablesize() at all — it is absent from the NDK headers, not merely hidden.
# PHP reaches for it under `#ifdef HAVE_UNISTD_H`, which is true on Android and still wrong.
# sysconf(_SC_OPEN_MAX) is the POSIX spelling of the same question.
sed -i.bak 's|dtablesize = getdtablesize();|dtablesize = (int)sysconf(_SC_OPEN_MAX);|' \
  ext/standard/php_fopen_wrapper.c

# bionic has no mblen() either, but it does have mbrlen() — which is what PHP already uses on
# its _REENTRANT path a few lines above. Passing NULL for the state gives mbrlen an internal
# static one, which is precisely what mblen() is. php_mb_reset() becomes a no-op; that only
# matters for stateful encodings, and this app is UTF-8 throughout.
sed -i.bak \
  -e 's|# define php_mblen(ptr, len) mblen(ptr, len)|# define php_mblen(ptr, len) ((int) mbrlen(ptr, len, NULL))|' \
  -e 's|# define php_mb_reset() php_ignore_value(mblen(NULL, 0))|# define php_mb_reset() ((void)0)|' \
  ext/standard/php_string.h

make -j"$(sysctl -n hw.ncpu 2>/dev/null || nproc)"

mkdir -p "$OUT"
"$STRIP" -o "$OUT/libphp.so" sapi/cli/php

# Prove the 16 KB alignment here rather than finding out at upload. Nothing else notices: the
# APK builds, installs and runs perfectly on a 4 KB device, and Play rejects it months later
# with "not compatible with 16 KB devices". One readelf is cheaper than that.
BAD=$("$TC/llvm-readelf" -l "$OUT/libphp.so" | awk '/LOAD/ {print $NF}' | sort -u | grep -vx '0x4000' || true)
if [ -n "$BAD" ]; then
  echo "libphp.so has LOAD segments aligned $BAD, not 0x4000 (16 KB)." >&2
  echo "Play rejects that for apps targeting Android 15+. Check -Wl,-z,max-page-size in LDFLAGS." >&2
  exit 1
fi

echo
echo "wrote $OUT/libphp.so ($(du -h "$OUT/libphp.so" | cut -f1)), LOAD segments 16 KB aligned"
echo "now: gradle assembleDebug"
