# GL-INET-6416A wifipineapple NANO V1.1.2
##需要手动设置eth0 eth1 如果要安装软件的话需要U盘扩展。
##默认集成了全部usb网卡。
##软件支持官方APP。
####
mkdir -p /mnt/sda2<br>
mount /dev/sda2 /mnt/sda2<br>
mkdir -p /tmp/cproot<br>
mount --bind / /tmp/cproot<br>
tar -C /tmp/cproot -cvf - . | tar -C /mnt/sda2 -xf -<br>
umount /tmp/cproot<br>
umount /mnt/sda2<br>



vi /etc/config/fstab //添加如下<br>

config 'mount'<br>
       option target '/'<br>
       option device '/dev/sda2'<br>
       option fstype 'ext4'<br>
       option options 'rw,sync'<br>
       option enabled '1'<br>
       option enabled_fsck '0'<br>

config 'swap'<br>
       option device '/dev/sda1'<br>
       option enabled '1'<br>

####