#!/bin/sh

cp rc.local /etc/

cd /tmp/
git clone https://github.com/6by9/rpi3-gpiovirtbuf.git
cd rpi3-gpiovirtbuf
make
mv rpi3-gpiovirtbuf /usr/local/bin/
cd ..
rm -rf rpi3-gpiovirtbuf
