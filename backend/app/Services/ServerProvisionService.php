<?php

namespace App\Services;

use App\Models\Server;

class ServerProvisionService
{
    public function generateBootstrapScript(Server $server): string
    {
        $phpVersion = $server->php_version;
        $dbType = $server->database_type;
        $sudoPassword = $server->sudo_password;

        return <<<BASH
#!/bin/bash
set -e

export DEBIAN_FRONTEND=noninteractive

# Update system
apt-get update -y
apt-get upgrade -y

# Install essential packages
apt-get install -y software-properties-common curl wget git unzip supervisor ufw

# Configure UFW
ufw allow 22
ufw allow 80
ufw allow 443
ufw --force enable

# Create gohan user
useradd -m -s /bin/bash gohan
echo "gohan:{$sudoPassword}" | chpasswd
usermod -aG sudo gohan
echo "gohan ALL=(ALL) NOPASSWD:ALL" >> /etc/sudoers.d/gohan

# Install Nginx
apt-get install -y nginx
systemctl enable nginx
systemctl start nginx

# Install PHP {$phpVersion}
add-apt-repository -y ppa:ondrej/php
apt-get update -y
apt-get install -y php{$phpVersion}-fpm php{$phpVersion}-cli php{$phpVersion}-common \
    php{$phpVersion}-mysql php{$phpVersion}-pgsql php{$phpVersion}-sqlite3 \
    php{$phpVersion}-curl php{$phpVersion}-gd php{$phpVersion}-mbstring \
    php{$phpVersion}-xml php{$phpVersion}-zip php{$phpVersion}-bcmath \
    php{$phpVersion}-intl php{$phpVersion}-readline php{$phpVersion}-redis

# Configure PHP-FPM
sed -i "s/;cgi.fix_pathinfo=1/cgi.fix_pathinfo=0/" /etc/php/{$phpVersion}/fpm/php.ini
systemctl enable php{$phpVersion}-fpm
systemctl restart php{$phpVersion}-fpm

# Install Composer
curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Install Node.js
curl -fsSL https://deb.nodesource.com/setup_20.x | bash -
apt-get install -y nodejs

# Install Redis
apt-get install -y redis-server
systemctl enable redis-server
systemctl start redis-server

# Install Database
BASH . ($dbType === 'mysql' ? $this->mysqlInstallScript() : $this->postgresInstallScript()) . <<<BASH

# Configure Supervisor
systemctl enable supervisor
systemctl start supervisor

# Set directory permissions
chown -R gohan:gohan /home/gohan
chmod 755 /home/gohan

echo "GOHAN_PROVISIONED=true" >> /etc/environment
BASH;
    }

    private function mysqlInstallScript(): string
    {
        return <<<'BASH'

apt-get install -y mysql-server
systemctl enable mysql
systemctl start mysql
BASH;
    }

    private function postgresInstallScript(): string
    {
        return <<<'BASH'

apt-get install -y postgresql postgresql-contrib
systemctl enable postgresql
systemctl start postgresql
BASH;
    }
}
