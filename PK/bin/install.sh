#!/bin/bash

PK_TARGET="/usr/local/bin/pk"

if [ -d "$PK_TARGET" ]; then
  echo "=> PK bin is already installed in $PK_TARGET"
  exit
fi

wget -O PK_TARGET http://github.com/norman784/Paket-Framework/tree/master/PK/bin/pk.sh

echo "=> You can now start using pk command type pk --help for mor information"