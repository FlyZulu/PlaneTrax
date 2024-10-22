# Plane Trax


## About

* Uses ADS-B data transmitted from aircraft to store information in a database about each takeoff and landing at a given airport.
 
* This uses data from an ADS-B receiver located near the airport and is designed to be executed on the same Raspberry Pi as the receiver.
 
* The aircraft position messages generated by dump1090 and dump978 in the form of json files are read by planetrax.php on the Raspberry Pi configured as the receiver.

* It is determined whether the aircraft position, heading, and altitude represent a takeoff or landing for a given runway at the selected airport.

* Since multiple messages will be received from dump1090 and dump978 for each takeoff and landing sequence, it is determined whether an operation has already been logged for this sequence or if this is a duplicate.  If not a duplicate, an entry is written to the ops table in the database.
 
* Optionally, log entries are written to a table in the database for all aircraft within a defined area.  This is used for debugging purposes.

* Determining the latitude / longitude limits for each runway for the airport is a key requirement for the logging to function accurately.  

*Installation Note: The instructions were compiled as the first installation was made and have not been tested.  Inconsistencies will likely be found in them.* 

* E-Mail to (info@planetrax.com) with questions.



# Table of contents

* [About](#about)
* [Prerequisites](#prerequisites)
* [Determining Latitude-Longitude Settings for Runways](#determining-latitude--longitude-settings-for-runways)
* [Installation](#installation)
  * [Web Server](#web-server)
  * [Database Server](#database-server)
  * [Plane Trax](#plane-trax)
  * [Service](#service)
  * [Test](#test)


# Prerequisites
1. An ADS-B receiver near the airport to be tracked.  This should be running on a Raspberry pi with dump1090 and / or dump978 installed.  This was tested on ADS-B Exchange build 8.2.220910 executing on a Raspberry Pi 4 but will likely work with other feeders.  See [ADSB Exchange Installation](https://www.adsbexchange.com/share-your-data/adsbx-custom-pi-image/#google_vignette) for installation instructions for the feeder.

2. PHP v7.4.33 or later

3. php7.4-mysql

4. MariaDB 10.5.23 or later

5. Web server - This was tested with lighttpd but others should work as well.

# Determining Latitude / Longitude Settings for Runways

* Making sure the minimum / maximum latitude and longitude are set correctly is a key factor in accurately identifying aircraft takeoffs and landings. 

* See the included file, airport.jpg, as an example.  The runways for this airport are 16 and 34.  

* Two boxes are created using min / max latitude and longitude.  Note that because the boxes end up being oriented to lines of latitude and longitude, they aren’t square to the runway unless the runway is either 18 / 36 or 09 / 27.

* The boxes should be about ¾ mile wide and 1 mile long with the long side as close to parallel to the runway as possible.

* The boxes shouldn’t include any of the runway area.  If so, duplicate landing / takeoff operations will be logged.

* In the supplied planetrax.conf file, the “a” coordinates are the runway 16 approach area / runway 34 departure area.   The “b” coordinates are the runway 34 approach area / runway 16 departure area.

# Installation

## Web Server

*Note: The Web Server is only required to view the sample output from the stats.php file.  This isn't required for data collection.* 

    sudo chmod /var/www/ 777 -R (This will allow user ‘pi’ to write files)

    md /var/www/html/planetrax

    chmod 777 /var/www/html/planetrax

    cp stats.php /var/www/html/planetrax/stats.php

    cp db_constants.php  /var/www/html/planetrax/db_constants.php

## Database Server
    sudo apt install mariadb-server

    sudo apt-get install php7.4-mysql

**Set user permissions**

    sudo mysql

    create user ‘pi’@’%’ identified by ‘adsb123’;

    GRANT ALL PRIVILEGES ON *.* TO 'pi'@'%';

**Create database and tables**

    mysql -upi -padsb123

    create database planetrax

    create table using create_table_ops.sql

    create table using create_table_ops_log.sql

**Enable remote access**

    cd /etc/mysql/mariadb.conf.d

    sudo nano 50-server.conf

    change the bind address from 127.0.0.1 to 0.0.0.0

    sudo service mariadb restart

## Plane Trax

    sudo cp  planetrax.conf /etc/planetrax.conf

    sudo nano planetrax.conf 

    set lat / lon parameters

    sudo chmod 777 /etc/planetrax.conf

    md /usr/local/planetrax

    chmod 777 /usr/local/planetrax

    cp planetrax.php /usr/local/planetrax/planetrax.php

    chmod 777 /usr/local/planetrax/planetrax.php

    cp db_constants.php  /usr/local/planetrax/db_constants.php

    chmod 777 /usr/local/planetrax/db_constants.php

## Service

    cp planetrax.service to /etc/systemd/system/planetrax.service

    sudo chmod 644 /etc/systemd/system/planetrax.service

    sudo systemctl enable planetrax.service

    sudo systemctl start planetrax.service

    sudo systemctl status planetrax.service

    sudo strace -e write -s 999 -p *id from status command*

## Test

    php /var/www/html/planetrax/stats.php

    http://*ip of pi*/planetrax/stats.php

