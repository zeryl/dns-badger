#!/bin/bash

#################################################################
#                        Initial checkup                        #
#################################################################

if [[ $EUID -ne 0 ]]; then
   printf "This installer must be run as root\n" 
   exit 1
fi

printf "\nWelcome to the dns-badger setup!\n"
printf "Thank you for being part of the dnstrace initiative.\n"
printf "  With <3 always, Chris\n\n"

#################################################################
#               Bandwidth and system usage checkup              #
#################################################################

printf "What is the maximum dns-badger QPS throughput? Any integer [5-95]\n"
printf "Per QPS, you'll need ~16 MB of RAM. Here are some suggested values:\n"
printf " [25] - VM with 512MB RAM; Raspberry Pi A+, B+, Zero\n"
printf " [50] - VM with 1GB RAM; Raspberry Pi 2, 3\n"
printf " [75] - VM with 1.5GB RAM\n"
printf " [95] - VM with 2 GB RAM\n"
printf "Footnotes: If you use Google DNS normally, do not input >50 QPS\n"
printf "Also, if this software is not on a dedicated VM/Pi, halve the suggested QPS\n"
printf "Selection: "

read input
if [[ "$input" -ge 5 && "$input" -le 95 ]]; then
	echo "$input" > maxThroughput
else
	printf "That's not a number between 5 and 95\n"
	exit
fi

#################################################################
#                       OS detection checkup                    #
#################################################################

printf "\nAttempting to automatically detect OS..."

if [ "CentOS" = "$(cat /etc/os-release | grep PRETTY_NAME= -m 1 | cut -d'"' -f2 | cut -d' ' -f1)" ]; then
        printf "Automatically using Centos from /etc/os-release"
        input="2"
elif [ "Debian" = "$(cat /etc/os-release | grep PRETTY_NAME= -m 1| cut -d'"' -f2 | cut -d' ' -f1)" ]; then
        printf "Automatically using Debian from /etc/os-release"
        input="1"
else
	printf "\nUnable to determine OS...running manual choice !\n\n"
	printf "\nWhat OS are we installing dns-badger on?\n"
	printf "Tip : It looks like your OS is \"$(cat /etc/os-release | grep PRETTY_NAME= -m 1| cut -d'"' -f2)\".\n\n"
	printf " [1] - Debian/Raspbian\n"
	printf " [2] - CentOS\n"
	printf "Selection: "
	read input
fi

#################################################################
#                       OS-specific install                     #
#################################################################

if [[ $input == "1" ]]; then
	printf "\n --- Updating and installing required packages... \n"
	apt-get update
	apt-get install -y php-cli curl php-curl php-json git unzip
elif [[ $input == "2" ]]; then
	printf "\n --- Updating and installing required packages... \n"
    
	#These are needed to get PHP5.6 
	rpm -Uvh https://dl.fedoraproject.org/pub/epel/epel-release-latest-7.noarch.rpm
	rpm -Uvh https://mirror.webtatic.com/yum/el7/webtatic-release.rpm
	
	yum update -y
    yum install -y php56w-cli curl php56w-common php56w git unzip
else
	printf "Input invalid, please restart installer\n"
	exit
fi

#################################################################
#               Folders and application setup                   #
#################################################################

user=$(stat -c '%U' setup.sh)
printf " --- Updating crontab for '$user'\n"

su -c 'crontab -l | { cat; echo "@reboot nohup bash $PWD/init.sh >> /tmp/dnsb-init.log 2>&1 &"; } | crontab -' $user
su -c 'crontab -l | { cat; echo "*/30 * * * * nohup php $PWD/reload.php >> /tmp/dnsb-rld.log 2>&1 &"; } | crontab -' $user

printf " --- Cloning dependencies from GitHub\n"
su -c 'mkdir $PWD/deps && git clone https://github.com/tweedge/phpqueues $PWD/deps/queues' $user

printf " --- Installing other dependencies via Composer\n"
su -c 'cd $PWD/deps && curl -sS https://getcomposer.org/installer | php' $user
su -c 'cd $PWD/deps && php composer.phar require layershifter/tld-extract' $user

printf " --- Creating extra files/folders/etc\n"
su -c 'mkdir $PWD/status' $user

printf " --- Generating and writing nodeID\n"
echo `cat /dev/urandom | tr -dc 'a-zA-Z0-9' | fold -w 32 | head -n 1` > nodeID

printf "\nWe're all set on this end. Thanks for waiting!\n"
printf "Please DM @tweedge on Slack with the following ID:\n"
printf "  `cat nodeID`\n"
printf "We'll get your node activated ASAP, and send back your extended API key!\n"
printf "You'll need to restart before your node starts. Want to do that now? [y/n] "

read input
if [[ $input == "y" || $input == "Y" ]]; then
	shutdown -r now
else
	printf "Don't forget to do that sometime soon! Your node won't run until then.\n"
fi

printf "REMINDER: SEND KEY ONLY OVER DM. It is a semi-secret key.\n"