# GL-INET-6416A wifipineapple NANO V1.1.2
##需要手动设置eth0 eth1 如果要安装软件的话需要U盘扩展。
##默认集成了全部usb网卡。
##软件支持官方APP。
##启用双网卡配置
####
vim /etc/config/network<br>

config interface 'loopback'<br>
        option ifname 'lo'<br>
        option proto 'static'<br>
        option ipaddr '127.0.0.1'<br>
        option netmask '255.0.0.0'<br>

config interface 'lan'<br>
        option ifname   'eth1'<br>
        option type     'bridge'<br>
        option proto    'static'<br>
        option ipaddr   '172.16.42.1'<br>
        option netmask  '255.255.255.0'<br>
        option gateway  '172.16.42.42'<br>
        option dns      '8.8.8.8, 8.8.4.4'<br>

config interface 'wan'<br>
        option ifname   'eth0'<br>
        option proto    'dhcp'<br>
        option dns      '8.8.8.8,8.8.4.4'<br>

config interface 'usb'<br>
        option ifname   'usb0'<br>
        option proto    'dhcp'<br>
        option dns      '8.8.8.8, 8.8.4.4'<br>
####
##扩展说明。
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