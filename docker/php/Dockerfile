FROM php:apache
MAINTAINER Harald Nezbeda <hn@nezhar.com>

# Enable apache mods.
RUN a2enmod rewrite

# Manually set up the apache environment variables
ENV APACHE_RUN_USER www-data
ENV APACHE_RUN_GROUP www-data
ENV APACHE_LOG_DIR /var/log/apache2
ENV APACHE_LOCK_DIR /var/lock/apache2
ENV APACHE_PID_FILE /var/run/apache2.pid

# Update the default apache config
RUN mkdir /var/www/app
ADD apache.conf /etc/apache2/sites-enabled/000-default.conf

# Set workdir
WORKDIR /var/www/app