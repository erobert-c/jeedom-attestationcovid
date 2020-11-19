#!/bin/bash
pdftk --version
if [ $? -ne 0 ]; then
    echo "nok"
else 
    echo "ok"
fi
exit 0