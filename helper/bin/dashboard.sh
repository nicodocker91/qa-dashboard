#!/usr/bin/env sh

# Author: Nicolas Giraud <nicolas.giraud.dev@gmail.com>
# Copyright (c) 2017-2019
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
# Name of the service the dashboard will be built.
SERVICE_NAME=$1
LOG_BUILD_PATH=/data/build/${SERVICE_NAME}/${BUILD_RELEASE}/logs

# Minimum value required from the global score to reach, otherwise PHP application will give you error an code.
# This value must be in the [0;100] interval and can be a float. Default to 0.
ACCEPTANCE_VALUE=${2:-0}
if [ "${ACCEPTANCE_VALUE}" -lt 0 -o "${ACCEPTANCE_VALUE}" -gt 100 ]; then
    >&2 echo "#Fatal: the acceptance value must be inside the interval [0;100]."
    >&2 echo "#Fatal: given value is ${ACCEPTANCE_VALUE}."
    >&2 echo ""
    exit 2
fi;

mkdir -p ${LOG_BUILD_PATH} 2>/dev/null
php -f ${__root}/helper/dashboard/dashboard.php build-release=${BUILD_RELEASE} \
    service=${SERVICE_NAME} \
    path-log=${LOG_BUILD_PATH} \
    acceptance-value=${ACCEPTANCE_VALUE}
