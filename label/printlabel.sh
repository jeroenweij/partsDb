#!/bin/bash

ID="$1"
MPN="$2"
TYPE="$3"
VALUE="$4"
LOC="$5"
ls -al

if [ -z "$ID" ]; then
    echo "\$ID is empty"
    exit 1
fi

if [ -z "$MPN" ]; then
    echo "\$MPN is empty"
    exit 1
fi

if [ -z "$TYPE" ]; then
    echo "\$TYPE is empty"
    exit 1
fi

if [ -z "$VALUE" ]; then
    echo "\$VALUE is empty"
    exit 1
fi

if [ -z "$LOC" ]; then
    echo "\$LOC is empty"
    exit 1
fi

BAR="EA${ID}?1P${MPN}?${TYPE}?${VALUE}?${LOC}"
D="\t"

echo -e "id${D}mpn${D}type${D}value${D}location${D}barcode" > data.csv
echo -e "${ID}${D}${MPN}${D}${TYPE}${D}${VALUE}${D}${LOC}${D}${BAR}" >> data.csv

glabels-3-batch --input=data.csv label.glabels 
rm data.csv

#lp -d DYMO-400 output.pdf
