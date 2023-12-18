# rpi-update af9cb14d5053f89857225bd18d1df59a089c171e
rpi-update
cp config.txt /boot/config.txt
cp cmdline.txt /boot/cmdline.txt
cp inittab /etc/inittab
cp tft35a-overlay.dtb /boot/overlays/
cp tft35a-overlay.dtb /boot/overlays/tft35a.dtbo
reboot

