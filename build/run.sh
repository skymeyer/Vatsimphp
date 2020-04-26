#!/usr/bin/env bash

set -o errexit
set -o nounset
set -o pipefail

[[ -L "${BASH_SOURCE[0]}" ]] \
    && REPO_ROOT="$(cd "$(dirname $(readlink "${BASH_SOURCE[0]}"))/.." && pwd -P)" \
    || REPO_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd -P)"

PHP_VERSION=${PHP_VERSION:-7.2}

# Execute composer command.
docker::composer() {
    local cmd=${1}
    mkdir -p ${HOME}/.composer
    docker run --rm -it -u $(id -u):$(id -g) \
        --volume ${REPO_ROOT}:/app \
        --volume ${COMPOSER_HOME:-$HOME/.composer}:/tmp \
        composer ${cmd}
}

# Execute command in PHP container.
docker::run() {
    local cmd="${@}"
    docker run --rm -it -u $(id -u):$(id -g) \
        --volume ${REPO_ROOT}:/app -w /app \
        php:${PHP_VERSION}-cli ${cmd}
}

# Run example code and output some as reference.
test-examples() {
    local cache=${1:-clear}
    if [[ "${cache}" == "clear" ]]; then
        rm -rf app/cache/*.txt
        rm -rf app/logs/*.log
    fi

    printf '%0.s=' {1..80}; echo
    echo "Testing easy_api_examples.php"
    printf '%0.s=' {1..80}; echo

    php examples/easy_api_examples.php
    test -f app/cache/vatsim-data.txt
    cat app/logs/vatsimphp.log
    printf '%0.s=' {1..80}; echo
    cat app/cache/status.txt
    printf '%0.s=' {1..80}; echo
    cat app/cache/metar-KSFO.txt
    echo

    printf '%0.s=' {1..80}; echo
    echo "Testing custom_logger.php"
    printf '%0.s=' {1..80}; echo

    php examples/custom_logger.php
    cat app/logs/vatsimphp_custom.log
}

"${@}"
