# PictureChallenge

Used to grab images for the weekly challenge:
https://www.reddit.com/r/PictureChallenge/


## Usage

To run the App locally you require Virtual Box and Vagrant and fallow the steps.

1. Clone repository
2. Fix the Mapping inside the **Homestead.yaml** to your local path (**folder->map**)
3. Set the fallowing entry inside your hosts file: **picturechallenge.app 192.168.10.10**
4. Setup the virtual machine: **vagrant up**
5. Access virtual machine via ssh" **vagrant ssh**
6. Go to the project directory: **cd /home/vagrant/picturechallenge**
7. Run composer install: **composer install**
8. Open **picturechallenge.app** in your browser


## Components

1. Flight PHP: https://github.com/mikecao/flight
2. Laravel Homestead: https://github.com/laravel/homestead
