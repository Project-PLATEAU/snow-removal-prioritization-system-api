#!/usr/bin/bash

DATA_DIR=$1

cp -r data/* ../../data/

cd ../../data/

ln -s $DATA_DIR/png .

ln -s $DATA_DIR/bld_components .
