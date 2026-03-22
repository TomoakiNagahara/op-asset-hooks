#!/usr/bin/env bash
## pre-push
#
# @created    2024-11-23 op-asset-git
# @moved      2025-11-13 op-asset-hooks
# @license    Apache-2.0
# @package    op-asset-hooks
# @copyright  (C) 2024 Tomoaki Nagahara

# Current hook
HOOK_NAME=`basename $0`

# Display current file path
REAL_PATH=$(realpath "$0")
DIR_PATH=$(cd $(dirname "$0") && pwd)
echo "Exetute: $DIR_PATH/$HOOK_NAME"

# Hook
$DIR_PATH/hook-the-hooks.sh ${HOOK_NAME}

# Check if status
if [ $? -ne 0 ]; then
   exit 1
fi

#  Search ci.sh
if [ -e "ci.sh" ]; then
  SOURCE="ci.sh"
elif [ -e ".ci.sh" ]; then
  SOURCE=".ci.sh"
else
  echo ".ci.sh does not exist."
  echo `pwd`
  exit 1
fi

#  Include ci.sh
echo "execute --> $SOURCE"
source "$SOURCE"
if [ $? -ne 0 ]; then
  exit 1
fi

#  Check prefix of commit message.
$DIR_PATH/pre-push-prefix.php
if [ $? -ne 0 ]; then
  exit 1
fi
