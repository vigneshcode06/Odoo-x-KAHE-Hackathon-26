FROM php:8.2-apache

# Install PDO MySQL extension
RUN docker-php-ext-install pdo pdo_mysql

# Enable Apache mod_rewrite for clean URLs if needed
RUN a2enmod rewrite

# Update the default apache site with the config we created.
# We will serve from /var/www/html which maps to our project root.
RUN echo '<Directory /var/www/html>\n\
    AllowOverride All\n\
</Directory>\n' >> /etc/apache2/apache2.conf

# Restart apache to apply changes
RUN service apache2 restart
