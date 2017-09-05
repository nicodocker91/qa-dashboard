#!/usr/bin/env sh

# Author: Nicolas Giraud <nicolas.giraud.dev@gmail.com>
# Copyright (c) 2017
# License: MIT

set -o errexit  # Make your script exit when a command fails.
set -o nounset  # Exit script when using unset variable.
#set -o xtrace   # Debug mode. Uncomment to activate.

__root="$(cd "$(dirname "${0}")" && pwd)/../.."

if [ $# -lt 1 ]; then
    echo "Dashboard error: you must define the name of the service or bundle."
    exit 1
fi

BUILD_RELEASE=$(date +'%Y%m%d0000')
SERVICE_NAME=$1
LOG_BUILD_PATH=/data/build/${SERVICE_NAME}/${BUILD_RELEASE}/logs

mkdir -p ${LOG_BUILD_PATH} 2>/dev/null
php -f ${__root}/helper/dashboard/dashboard.php build-release=${BUILD_RELEASE} service=${SERVICE_NAME} path-log=${LOG_BUILD_PATH}
