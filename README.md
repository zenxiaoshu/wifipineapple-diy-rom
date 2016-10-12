# GL-INET-6416A wifipineapple NANO V1.1.2
##需要手动设置eth0 eth1 如果要安装软件的话需要U盘扩展。
##默认集成了全部usb网卡。
##软件支持官方APP。
####
mkdir -p /mnt/sda2
mount /dev/sda2 /mnt/sda2
mkdir -p /tmp/cproot
mount --bind / /tmp/cproot
tar -C /tmp/cproot -cvf - . | tar -C /mnt/sda2 -xf -
umount /tmp/cproot
umount /mnt/sda2



vi /etc/config/fstab //添加如下

config 'mount'
       option target '/'
       option device '/dev/sda2'
       option fstype 'ext4'
       option options 'rw,sync'
       option enabled '1'
       option enabled_fsck '0'

config 'swap'
       option device '/dev/sda1'
       option enabled '1'

####