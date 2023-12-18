# Edit /etc/dhcpcd.conf as follows to make IP static:
# interface wlan0
#       static ip_address=192.168.1.150/24
#       static routers=192.168.1.1
#       static domain_name_servers=192.168.1.1 1.1.1.1

sed -i "$ a\deb http://ftp.debian.org/debian stretch-backports main" /etc/apt/sources.list
apt-key adv --recv-key --keyserver keyserver.ubuntu.com "8B48AD6246925553"
apt update
apt install python-serial python-numpy libqt5core5a qt5-qmake qtbase5-dev-tools qtbase5-dev libqt5widgets5 cmake php libapache2-mod-php apache2 dirmngr -y
# apt install libqt5charts5 libqt5charts5-dev -y

cd /tmp
wget https://launchpad.net/~trebelnik-stefina/+archive/ubuntu/radeon-profile/+files/libqt5charts5-dev_5.7.1~zesty_armhf.deb
wget https://launchpad.net/~trebelnik-stefina/+archive/ubuntu/radeon-profile/+files/libqt5charts5_5.7.1~zesty_armhf.deb
dpkg -i libqt5charts5*

wget https://github.com/rogersce/cnpy/archive/master.zip
unzip master.zip
cd master
make install

apt install certbot python-certbot-apache -t stretch-backports -y --force-yes
