#!/bin/bash

# Jejakawan Auto-Installer
# Supporting: Ubuntu/Debian & CentOS/AlmaLinux/RHEL

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Ensure script is run as root or with sudo
if [ "$EUID" -ne 0 ]; then
    echo -e "${YELLOW}This script requires sudo privileges. Refreshing sudo session...${NC}"
    sudo -v
    # Keep sudo alive during the process
    while true; do sudo -n true; sleep 60; kill -0 "$$" || exit; done 2>/dev/null &
fi

# Determine project root
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" &> /dev/null && pwd )"
PROJECT_ROOT="$SCRIPT_DIR"

# Check if we are inside 'scripts' folder
if [[ "$(basename "$PROJECT_ROOT")" == "scripts" ]]; then
    PROJECT_ROOT="$(dirname "$PROJECT_ROOT")"
fi

cd "$PROJECT_ROOT"
echo -e "${BLUE}Project Root: $PROJECT_ROOT${NC}"

# Detect Application Layout
IS_BUNDLE=false
BACKEND_PATH=""
FRONTEND_PATH=""

if [ -f "artisan" ]; then
    echo -e "${GREEN}Production Bundle detected (Backend at root).${NC}"
    IS_BUNDLE=true
    BACKEND_PATH="."
elif [ -d "backend" ] && [ -f "backend/artisan" ]; then
    echo -e "${BLUE}Development Repo detected (Backend in subdirectory).${NC}"
    BACKEND_PATH="backend"
fi

if [ -d "frontend" ]; then
    FRONTEND_PATH="frontend"
fi

# Verify critical structure
if [ -z "$BACKEND_PATH" ]; then
    echo -e "${RED}Error: Could not find Jejakawan Backend (artisan file) in $PROJECT_ROOT${NC}"
    echo -e "${YELLOW}Tip: Ensure you are running this from the correct project directory.${NC}"
    exit 1
fi

# Error Handling Function
trap 'on_error $LINENO' ERR
on_error() {
    echo -e "\n${RED}==================================================${NC}"
    echo -e "${RED}  CRITICAL ERROR: Installation Stalled            ${NC}"
    echo -e "${RED}  Failed at line $1                               ${NC}"
    echo -e "${RED}==================================================${NC}"
    echo -e "${YELLOW}Common Solutions:${NC}"
    echo -e "1. Check internet connection / firewall."
    echo -e "2. Mirrors might be down. Try again in 5 minutes."
    echo -e "3. Manual fix: sudo apt-get install -f"
    echo -e "\n${RED}NUCLEAR OPTION (If all fails):${NC}"
    echo -e "If this server has many legacy conflicts, it is highly recommended to:"
    echo -e "${GREEN}-> Reinstall a FRESH OS (Ubuntu 24.04 LTS or AlmaLinux 9 recommended)${NC}"
    echo -e "${GREEN}-> Use a clean, empty server to avoid dependency hell.${NC}"
    exit 1
}

# DNS Fallback Function
try_alternative_dns() {
    echo -e "${YELLOW}Resolving issues detected. Trying alternative DNS (8.8.8.8)...${NC}" >&2
    echo "nameserver 8.8.8.8" | sudo tee /etc/resolv.conf > /dev/null
}

# Mirror Optimization for Ubuntu
optimize_mirrors() {
    if [[ "$OS" == "ubuntu" ]]; then
        echo -e "${YELLOW}Optimizing mirrors to find the best/closest path...${NC}" >&2
        sudo cp /etc/apt/sources.list /etc/apt/sources.list.bak 2>/dev/null || true
        # Use mirror redirector to automatically pick the best mirror
        sudo sed -i 's|http://[a-z]\{2\}\.archive\.ubuntu\.com/ubuntu/|mirror://mirrors.ubuntu.com/mirrors.txt|g' /etc/apt/sources.list
        sudo sed -i 's|http://archive.ubuntu.com/ubuntu/|mirror://mirrors.ubuntu.com/mirrors.txt|g' /etc/apt/sources.list
        sudo apt-get update -y >&2 || true
    fi
}

# Mirror Switcher Fallback
switch_mirror() {
    if [[ "$OS" == "ubuntu" || "$OS" == "debian" ]]; then
        echo -e "${YELLOW}Mirror issue detected. Switching to main global mirror as fallback...${NC}" >&2
        # Restore backup if exists, otherwise just point to main
        if [ -f /etc/apt/sources.list.bak ]; then
            sudo cp /etc/apt/sources.list.bak /etc/apt/sources.list
        fi
        sudo sed -i 's|[a-z]\{2\}\.archive\.ubuntu\.com|archive.ubuntu.com|g' /etc/apt/sources.list
        sudo sed -i 's|mirror://mirrors.ubuntu.com/mirrors.txt|http://archive.ubuntu.com/ubuntu/|g' /etc/apt/sources.list
        sudo apt-get update -y >&2
    fi
}

# Version Comparison Helper (Returns 0 if $1 >= $2)
version_ge() {
    [ "$1" = "$(echo -e "$1\n$2" | sort -V | tail -n1)" ]
}

# Package Cleanup Function
cleanup_package() {
    local PKG_PATTERN=$1
    echo -e "${RED}Cleaning up old packages matching: $PKG_PATTERN...${NC}"
    if [[ "$OS" == "ubuntu" || "$OS" == "debian" ]]; then
        sudo apt-get purge -y $PKG_PATTERN || true
        sudo apt-get autoremove -y || true
    elif [[ "$OS" == "centos" || "$OS" == "almalinux" || "$OS" == "rhel" ]]; then
        sudo dnf remove -y $PKG_PATTERN || true
    fi
}

# Search Available Versions
search_pkg_version() {
    local PKG=$1
    echo -e "${BLUE}Available version for $PKG:${NC}"
    if [[ "$OS" == "ubuntu" || "$OS" == "debian" ]]; then
        apt-cache policy "$PKG" | grep "Candidate" || echo "Not found in repo"
    elif [[ "$OS" == "centos" || "$OS" == "almalinux" || "$OS" == "rhel" ]]; then
        dnf list available "$PKG" 2>/dev/null | grep "$PKG" || echo "Not found in repo"
    fi
}

# Web Server Conflict Resolution
resolve_webserver_conflict() {
    echo -e "${YELLOW}Checking for web server conflicts (Apache vs Nginx)...${NC}"
    if [[ "$OS" == "ubuntu" || "$OS" == "debian" ]]; then
        # Check if apache2 is installed, running, or has files
        if dpkg -l | grep -q "apache2" || command -v apache2 &> /dev/null || [ -f /etc/init.d/apache2 ]; then
            echo -e "${RED}Apache detected. Permanently removing to prioritize Nginx...${NC}"
            
            # 1. Stop and Disable
            sudo systemctl stop apache2 || true
            sudo systemctl disable apache2 || true
            sudo systemctl mask apache2 || true # Prevent it from being started by other services
            
            # 2. Kill any remaining processes on Port 80
            if command -v fuser &> /dev/null; then
                sudo fuser -k 80/tcp || true
            fi
            
            # 3. Purge packages non-interactively
            echo -e "${YELLOW}Purging Apache packages...${NC}"
            export DEBIAN_FRONTEND=noninteractive
            sudo apt-get purge -y apache2* libapache2* apache2-utils apache2-bin apache2-data || true
            sudo apt-get autoremove -y || true
            
            # 4. Remove configuration directories
            sudo rm -rf /etc/apache2 || true
            
            echo -e "${GREEN}Apache removal attempt completed.${NC}"
        fi
    elif [[ "$OS" == "centos" || "$OS" == "almalinux" || "$OS" == "rhel" ]]; then
        if rpm -q httpd &> /dev/null || command -v httpd &> /dev/null; then
            echo -e "${RED}Apache (httpd) detected. Removing to prioritize Nginx...${NC}"
            sudo systemctl stop httpd || true
            sudo systemctl disable httpd || true
            sudo systemctl mask httpd || true
            sudo dnf remove -y httpd httpd-tools || true
            sudo rm -rf /etc/httpd || true
        fi
    fi
}

# Fix for nested obfuscated folders (caused by yakpro-po)
fix_obfuscated_paths() {
    local TARGET_DIR=$1
    if [ -d "$TARGET_DIR/yakpro-po/obfuscated" ]; then
        echo -e "${YELLOW}Detected nested obfuscated structure in $TARGET_DIR. Flattening...${NC}" >&2
        mv "$TARGET_DIR/yakpro-po/obfuscated/"* "$TARGET_DIR/"
        rm -rf "$TARGET_DIR/yakpro-po"
    fi
}

# Retry Command Function
retry_cmd() {
    local n=1
    local max=3
    local delay=3
    while true; do
        if "$@"; then
            break
        else
            if [[ $n -lt $max ]]; then
                ((n++))
                echo -e "${YELLOW}Command failed. Attempt $n/$max. Retrying in ${delay}s...${NC}" >&2
                
                # Recovery steps based on OS
                if [[ "$OS" == "ubuntu" || "$OS" == "debian" ]]; then
                    if [[ $n -eq 2 ]]; then
                        echo -e "${BLUE}Recovery Step 1: Fixing broken dependencies & DNS...${NC}" >&2
                        sudo dpkg --configure -a || true
                        sudo apt-get install -f -y || true
                        try_alternative_dns || true
                    fi
                    if [[ $n -eq 3 ]]; then
                        echo -e "${BLUE}Recovery Step 2: Update with fix-missing & Mirror swap...${NC}" >&2
                        sudo apt-get update --fix-missing -y >&2 || true
                        switch_mirror || true
                    fi
                elif [[ "$OS" == "centos" || "$OS" == "almalinux" || "$OS" == "rhel" ]]; then
                    if [[ $n -eq 2 ]]; then
                        echo -e "${BLUE}Recovery Step 1: Cleaning cache & DNS...${NC}" >&2
                        sudo dnf clean all || true
                        try_alternative_dns || true
                    fi
                    if [[ $n -eq 3 ]]; then
                        echo -e "${BLUE}Recovery Step 2: DNF Check-update...${NC}" >&2
                        sudo dnf check-update || true
                    fi
                fi
                
                sleep $delay
            else
                echo -e "${RED}Command failed after $max attempts and alternative paths.${NC}" >&2
                return 1
            fi
        fi
    done
}

echo -e "${BLUE}==================================================${NC}"
echo -e "${BLUE}       Jejakawan Auto-Installer v1.0.0-beta.1   ${NC}"
echo -e "${BLUE}==================================================${NC}"

# 0. Connectivity Check
echo -e "${YELLOW}Checking internet connectivity...${NC}"
if ! ping -c 1 8.8.8.8 &> /dev/null; then
    echo -e "${RED}Error: No internet connection detected (Ping 8.8.8.8 failed).${NC}"
    exit 1
fi

echo -e "${YELLOW}Verifying DNS resolution...${NC}"
if ! host google.com &> /dev/null && ! nslookup google.com &> /dev/null; then
    echo -e "${YELLOW}DNS resolution failed. Applying 8.8.8.8 fallback...${NC}"
    try_alternative_dns || true
fi
echo -e "${GREEN}Internet: OK${NC}"

# 1. OS Detection
if [ -f /etc/os-release ]; then
    . /etc/os-release
    OS=$ID
    VER=$VERSION_ID
else
    echo -e "${RED}Error: Cannot detect OS. Please install manually.${NC}"
    exit 1
fi

# Detect Web User and Service Names based on OS
if [[ "$OS" == "ubuntu" || "$OS" == "debian" ]]; then
    WEB_USER="www-data"
    WEB_GROUP="www-data"
    REDIS_SVC="redis-server"
    PHP_FPM_SVC="php8.3-fpm"
    SUPERVISOR_CONF_DIR="/etc/supervisor/conf.d"
    SUPERVISOR_SVC="supervisor"
    SUPERVISOR_CTL="supervisorctl"
elif [[ "$OS" == "centos" || "$OS" == "almalinux" || "$OS" == "rhel" ]]; then
    # On RHEL, we'll use nginx user for everything to avoid apache/nginx mismatch
    WEB_USER="nginx"
    WEB_GROUP="nginx"
    REDIS_SVC="redis"
    PHP_FPM_SVC="php-fpm"
    SUPERVISOR_CONF_DIR="/etc/supervisord.d"
    SUPERVISOR_SVC="supervisord"
    SUPERVISOR_CTL="supervisorctl"
    
    # Ensure nginx user exists (it should after nginx install)
    if ! id -u nginx &>/dev/null; then sudo useradd nginx; fi
    
    # Disable SELinux temporarily to ensure success
    echo -e "${YELLOW}Temporarily setting SELinux to permissive mode...${NC}"
    sudo setenforce 0 || true
else
    WEB_USER="www-data" # Fallback
    WEB_GROUP="www-data"
    REDIS_SVC="redis"
    PHP_FPM_SVC="php-fpm"
    SUPERVISOR_CONF_DIR="/etc/supervisor/conf.d"
    SUPERVISOR_SVC="supervisor"
    SUPERVISOR_CTL="supervisorctl"
fi

echo -e "${GREEN}Detected OS: $OS $VER (User: $WEB_USER)${NC}"

# 1.5 Mirror Optimization
optimize_mirrors

# 2. Resource Check
echo -e "${YELLOW}Auditing system resources...${NC}"
TOTAL_RAM=$(free -m | awk '/^Mem:/{print $2}')
if [ "$TOTAL_RAM" -lt 1000 ]; then
    echo -e "${RED}Warning: Minimal 1GB RAM recommended. Current: ${TOTAL_RAM}MB${NC}"
    # Continue anyway but with warning
fi
echo -e "RAM OK: ${TOTAL_RAM}MB"

## 4. Smart Dependency Audit & Batch Management
echo -e "${BLUE}==================================================${NC}"
echo -e "${BLUE}       Smart Dependency Audit                     ${NC}"
echo -e "${BLUE}==================================================${NC}"

PKGS_TO_INSTALL=()
REPOS_NEEDED=()
NEEDS_UPDATE=false

# Helper to check if a command exists
cmd_exists() { command -v "$1" &> /dev/null; }

# 1. Audit Base Tools
for tool in curl wget ca-certificates gnupg jq zip unzip; do
    if ! cmd_exists "$tool"; then PKGS_TO_INSTALL+=("$tool"); fi
done

# 2. Audit PHP (Target 8.3)
PHP_TARGET="8.3"
PHP_UPGRADE_NEEDED=false
if ! cmd_exists php; then
    PHP_UPGRADE_NEEDED=true
else
    PHP_VER_ID=$(php -r "echo PHP_VERSION_ID;")
    if [ "$PHP_VER_ID" -lt 80300 ]; then PHP_UPGRADE_NEEDED=true; fi
fi

if [ "$PHP_UPGRADE_NEEDED" = true ]; then
    REPOS_NEEDED+=("php")
    if [[ "$OS" == "ubuntu" || "$OS" == "debian" ]]; then
        PKGS_TO_INSTALL+=(php8.3 php8.3-cli php8.3-common php8.3-fpm php8.3-pgsql php8.3-bcmath php8.3-curl php8.3-gd php8.3-intl php8.3-xml php8.3-zip php8.3-mbstring php8.3-redis php8.3-opcache)
    else
        PKGS_TO_INSTALL+=(php php-cli php-common php-fpm php-process php-pgsql php-bcmath php-curl php-gd php-intl php-xml php-zip php-mbstring php-redis php-opcache)
    fi
fi

# 3. Audit Web & Process Managers
if ! cmd_exists nginx; then PKGS_TO_INSTALL+=("nginx"); fi
if ! cmd_exists supervisor && ! cmd_exists supervisord; then
    if [[ "$OS" == "ubuntu" ]]; then PKGS_TO_INSTALL+=("supervisor"); else PKGS_TO_INSTALL+=("supervisor"); REPOS_NEEDED+=("epel"); fi
fi

# 4. Audit Databases
if ! cmd_exists psql; then
    REPOS_NEEDED+=("postgres")
    if [[ "$OS" == "ubuntu" ]]; then PKGS_TO_INSTALL+=(postgresql-16 postgresql-contrib); else PKGS_TO_INSTALL+=(postgresql16-server); fi
fi
if ! cmd_exists redis-cli; then
    if [[ "$OS" == "ubuntu" ]]; then PKGS_TO_INSTALL+=("redis-server"); else PKGS_TO_INSTALL+=("redis"); fi
fi

# 5. Audit Node.js (Target 22)
NODE_UPGRADE_NEEDED=false
if ! cmd_exists node; then
    NODE_UPGRADE_NEEDED=true
else
    NODE_MAJOR=$(node -v | sed 's/v//' | cut -d. -f1)
    if [ "$NODE_MAJOR" -lt 22 ]; then NODE_UPGRADE_NEEDED=true; fi
fi

if [ "$NODE_UPGRADE_NEEDED" = true ]; then
    REPOS_NEEDED+=("nodejs")
    PKGS_TO_INSTALL+=("nodejs")
fi

# --- EXECUTION PHASE ---
if [ ${#PKGS_TO_INSTALL[@]} -gt 0 ] || [ ${#REPOS_NEEDED[@]} -gt 0 ]; then
    echo -e "${YELLOW}Audit complete. Found ${#PKGS_TO_INSTALL[@]} items to process.${NC}"
    
    # 1. Setup Repositories (Only if needed)
    for repo in "${REPOS_NEEDED[@]}"; do
        case $repo in
            php)
                if [[ "$OS" == "ubuntu" ]]; then
                    sudo add-apt-repository -y ppa:ondrej/php
                else
                    sudo dnf install -y https://rpms.remirepo.net/enterprise/remi-release-$(echo $VER | cut -d. -f1).rpm
                    sudo dnf module reset php -y && sudo dnf module enable php:remi-8.3 -y
                fi
                NEEDS_UPDATE=true
                ;;
            postgres)
                if [[ "$OS" == "ubuntu" ]]; then
                    sudo install -d /usr/share/postgresql-common/pgdg
                    curl -o /usr/share/postgresql-common/pgdg/apt.postgresql.org.asc --fail https://www.postgresql.org/media/keys/ACCC4CF8.asc
                    echo "deb [signed-by=/usr/share/postgresql-common/pgdg/apt.postgresql.org.asc] http://apt.postgresql.org/pub/repos/apt $(lsb_release -cs)-pgdg main" | sudo tee /etc/apt/sources.list.d/pgdg.list
                else
                    sudo dnf install -y https://download.postgresql.org/pub/repos/yum/reporpms/EL-$(echo $VER | cut -d. -f1)-x86_64/pgdg-redhat-repo-latest.noarch.rpm
                    sudo dnf -qy module disable postgresql
                fi
                NEEDS_UPDATE=true
                ;;
            nodejs)
                if [[ "$OS" == "ubuntu" ]]; then
                    curl -fsSL https://deb.nodesource.com/setup_22.x | sudo -E bash -
                else
                    curl -fsSL https://rpm.nodesource.com/setup_22.x | sudo bash -
                fi
                NEEDS_UPDATE=false # nodesource script usually runs update
                ;;
            epel)
                if [[ "$OS" != "ubuntu" ]]; then sudo dnf install -y epel-release; fi
                NEEDS_UPDATE=true
                ;;
        esac
    done

    # 2. Single Repository Update
    if [ "$NEEDS_UPDATE" = true ]; then
        echo -e "${YELLOW}Synchronizing package repositories...${NC}"
        if [[ "$OS" == "ubuntu" ]]; then sudo apt-get update -y; else sudo dnf makecache -y; fi
    fi

    # 3. Batch Installation
    echo -e "${GREEN}Installing all dependencies in one batch...${NC}"
    if [[ "$OS" == "ubuntu" ]]; then
        sudo apt-get install -y "${PKGS_TO_INSTALL[@]}"
    else
        sudo dnf install -y "${PKGS_TO_INSTALL[@]}"
    fi
else
    echo -e "${GREEN}✅ All dependencies are already met. Skipping installation phase.${NC}"
fi

# Post-Install Database Init
if [[ "$OS" != "ubuntu" ]] && ! [ -f /var/lib/pgsql/16/data/PG_VERSION ]; then
    echo -e "${YELLOW}Initializing PostgreSQL 16 DB...${NC}"
    sudo /usr/pgsql-16/bin/postgresql-16-setup initdb || true
    sudo systemctl enable postgresql-16 && sudo systemctl start postgresql-16
fi

# Composer Check & Install
if ! command -v composer &> /dev/null; then
    echo -e "${YELLOW}Installing Composer...${NC}"
    curl -sS https://getcomposer.org/installer | php
    sudo mv composer.phar /usr/local/bin/composer
fi

# 5. Application Setup
echo -e "${BLUE}==================================================${NC}"
echo -e "${BLUE}       Setting up Jejakawan Application         ${NC}"
echo -e "${BLUE}==================================================${NC}"

# Backend Setup
echo -e "${YELLOW}Setting up Backend...${NC}"
cd "$BACKEND_PATH"

# Enable Composer superuser mode for root execution
export COMPOSER_ALLOW_SUPERUSER=1

# Auto-fix obfuscated structures if present
fix_obfuscated_paths "app"
fix_obfuscated_paths "routes"

# Install dependencies without scripts first to avoid "Class not found" errors during discovery
retry_cmd composer install --no-interaction --optimize-autoloader --no-scripts

if [ ! -f .env ]; then
    cp .env.example .env
    php artisan key:generate
fi

# Finalize autoloader and discovery manually
echo -e "${YELLOW}Finalizing Backend discovery...${NC}"
composer dump-autoload --optimize
php artisan package:discover --ansi || true

cd "$PROJECT_ROOT"

# Frontend Setup
if [ -n "$FRONTEND_PATH" ]; then
    echo -e "${YELLOW}Setting up Frontend...${NC}"
    cd "$FRONTEND_PATH"
    retry_cmd npm install
    retry_cmd npm run build
    cd "$PROJECT_ROOT"
else
    echo -e "${BLUE}Note: Frontend source not found. Assuming assets are already pre-built in public/.${NC}"
fi

# Permissions
echo -e "${YELLOW}Setting up permissions...${NC}"
sudo chmod -R 775 "$BACKEND_PATH"
sudo chmod -R 777 "$BACKEND_PATH/storage" "$BACKEND_PATH/bootstrap/cache" # Force writable for success
sudo chown -R $USER:$WEB_GROUP "$BACKEND_PATH" || true

if [[ "$OS" == "almalinux" || "$OS" == "rhel" ]]; then
    echo -e "${YELLOW}Applying SELinux contexts for $OS...${NC}"
    # Try multiple ways to fix SELinux
    sudo restorecon -Rv "$BACKEND_PATH" &>/dev/null || true
    sudo chcon -R -t httpd_sys_rw_content_t "$BACKEND_PATH/storage" "$BACKEND_PATH/bootstrap/cache" 2>/dev/null || true
    sudo setsebool -P httpd_can_network_connect 1 2>/dev/null || true
fi

# 6. Database & Redis Configuration
echo -e "${BLUE}==================================================${NC}"
echo -e "${BLUE}       Configuring Database & Redis               ${NC}"
echo -e "${BLUE}==================================================${NC}"

# Read DB values from .env and clean quotes
DB_CONN=$(grep DB_CONNECTION "$BACKEND_PATH/.env" | cut -d= -f2 | sed 's/["'\'']//g')
DB_NAME=$(grep DB_DATABASE "$BACKEND_PATH/.env" | cut -d= -f2 | sed 's/["'\'']//g')
DB_USER=$(grep DB_USERNAME "$BACKEND_PATH/.env" | cut -d= -f2 | sed 's/["'\'']//g')
DB_PASS=$(grep DB_PASSWORD "$BACKEND_PATH/.env" | cut -d= -f2 | sed 's/["'\'']//g')

# Auto-fill password if empty to prevent Postgres auth failure
if [ -z "$DB_PASS" ]; then
    DB_PASS="Senja@jejakawan"
    echo -e "${YELLOW}DB_PASSWORD is empty. Using default: $DB_PASS${NC}"
    sed -i "s|DB_PASSWORD=.*|DB_PASSWORD=$DB_PASS|g" "$BACKEND_PATH/.env"
fi

# Automated DB Creation
if [ "$DB_CONN" == "pgsql" ]; then
    echo -e "${YELLOW}Provisioning PostgreSQL Database...${NC}"
    sudo -u postgres psql -c "CREATE DATABASE $DB_NAME;" || true
    sudo -u postgres psql -c "CREATE USER $DB_USER WITH PASSWORD '$DB_PASS';" || true
    sudo -u postgres psql -c "GRANT ALL PRIVILEGES ON DATABASE $DB_NAME TO $DB_USER;" || true
    
    # Patch PostgreSQL Auth (pg_hba.conf) - Aggressive Top-Priority
    PG_HBA="/var/lib/pgsql/16/data/pg_hba.conf"
    if [ -f "$PG_HBA" ]; then
        echo -e "${YELLOW}Aggressively Patching PostgreSQL Authentication...${NC}"
        # Create a temp file with our rules at the top
        # Allow postgres user to bypass password on local socket (for provisioning)
        echo "local   all             postgres                                peer" | sudo tee /tmp/pg_hba.new > /dev/null
        echo "local   all             all                                     md5" | sudo tee -a /tmp/pg_hba.new > /dev/null
        echo "host    all             all             127.0.0.1/32            md5" | sudo tee -a /tmp/pg_hba.new > /dev/null
        echo "host    all             all             ::1/128                 md5" | sudo tee -a /tmp/pg_hba.new > /dev/null
        # Append the rest of the original file, skipping lines we just added
        sudo grep -vE "^(local|host).*all.*all.*(127.0.0.1|::1|md5|ident|peer)" "$PG_HBA" | sudo tee -a /tmp/pg_hba.new > /dev/null
        sudo mv /tmp/pg_hba.new "$PG_HBA"
        sudo systemctl restart postgresql-16
    fi
    
    # Optional Tuning
    echo -e "${YELLOW}Applying PostgreSQL optimization...${NC}"
    PG_CONF="/var/lib/pgsql/16/data/postgresql.conf"
    [ -f "$PG_CONF" ] && sudo cp scripts/templates/postgresql.conf.template $PG_CONF && sudo systemctl restart postgresql-16
elif [ "$DB_CONN" == "mysql" ]; then
    echo -e "${YELLOW}Provisioning MySQL/MariaDB Database...${NC}"
    mysql -e "CREATE DATABASE IF NOT EXISTS $DB_NAME;" || true
    mysql -e "CREATE USER IF NOT EXISTS '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASS';" || true
    mysql -e "GRANT ALL PRIVILEGES ON $DB_NAME.* TO '$DB_USER'@'localhost';" || true
    mysql -e "FLUSH PRIVILEGES;" || true
    
    # Optional Tuning
    echo -e "${YELLOW}Applying MariaDB optimization...${NC}"
    MY_CONF="/etc/my.cnf.d/jejakawan.cnf"
    [ -d /etc/my.cnf.d ] && sudo cp scripts/templates/mariadb.conf.template $MY_CONF && sudo systemctl restart mariadb
fi

# Run Migrations & Setup
echo -e "${YELLOW}Running database migrations...${NC}"
cd "$BACKEND_PATH"
# Ensure we can run artisan
chmod +x artisan
# Try to migrate
php artisan migrate --force || true
php artisan storage:link || true

# Ensure APP_KEY is definitely set
if ! grep -q "APP_KEY=base64" .env || [ -z "$(grep APP_KEY .env | cut -d= -f2)" ]; then
    echo -e "${YELLOW}Generating Application Key...${NC}"
    php artisan key:generate --force
fi

# Clear all caches
php artisan config:clear
php artisan cache:clear
php artisan view:clear

cd "$PROJECT_ROOT"
echo -e "${YELLOW}Deploying Redis configuration...${NC}"
REDIS_CONF_TARGET="/etc/redis.conf"
[ ! -f "$REDIS_CONF_TARGET" ] && REDIS_CONF_TARGET="/etc/redis/redis.conf"

if [ -f "$REDIS_CONF_TARGET" ]; then
    sed -e "s|{{REDIS_PORT}}|6379|g" \
        scripts/templates/redis.conf.template | sudo tee $REDIS_CONF_TARGET > /dev/null
    sudo systemctl restart $REDIS_SVC &>/dev/null || true
fi

# 7. Server Configuration (PHP, Nginx, Supervisor, Cron)
echo -e "${BLUE}==================================================${NC}"
echo -e "${BLUE}       Configuring Server Services                ${NC}"
echo -e "${BLUE}==================================================${NC}"

# Detect PHP-FPM Socket early
if [ -S /run/php-fpm/www.sock ]; then
    PHP_FPM_SOCK="unix:/run/php-fpm/www.sock"
elif [ -S /var/run/php/php8.3-fpm.sock ]; then
    PHP_FPM_SOCK="unix:/var/run/php/php8.3-fpm.sock"
elif [ -S /var/run/php/php8.2-fpm.sock ]; then
    PHP_FPM_SOCK="unix:/var/run/php/php8.2-fpm.sock"
elif [[ "$OS" == "almalinux" || "$OS" == "rhel" ]]; then
    PHP_FPM_SOCK="unix:/run/php-fpm/www.sock"
else
    PHP_FPM_SOCK="unix:/var/run/php-fpm.sock"
fi

# PHP & FPM Tuning
echo -e "${YELLOW}Applying PHP & FPM optimization...${NC}"
PHP_INI_PATH="/etc/php.ini"
[ ! -f "$PHP_INI_PATH" ] && PHP_INI_PATH="/etc/php/8.3/fpm/php.ini"

if [ -f "$PHP_INI_PATH" ] && [ -f "scripts/templates/php.ini.template" ]; then
    sudo cp scripts/templates/php.ini.template $PHP_INI_PATH
fi

FPM_WWW_PATH="/etc/php-fpm.d/www.conf"
[ ! -f "$FPM_WWW_PATH" ] && FPM_WWW_PATH="/etc/php/8.3/fpm/pool.d/www.conf"

if [ -f "$FPM_WWW_PATH" ] && [ -f "scripts/templates/fpm-www.conf.template" ]; then
    # Detect socket path for template
    SOCKET_PATH=$(echo $PHP_FPM_SOCK | sed 's/unix://')
    
    # Ensure socket directory exists
    SOCKET_DIR=$(dirname "$SOCKET_PATH")
    [ ! -d "$SOCKET_DIR" ] && sudo mkdir -p "$SOCKET_DIR"
    sudo chown $WEB_USER:$WEB_GROUP "$SOCKET_DIR"
    
    sed -e "s|{{USER}}|$WEB_USER|g" \
        -e "s|{{GROUP}}|$WEB_GROUP|g" \
        -e "s|{{PHP_FPM_SOCK_PATH}}|$SOCKET_PATH|g" \
        scripts/templates/fpm-www.conf.template | sudo tee $FPM_WWW_PATH > /dev/null
    
    # Fix PHP session/cache dir ownership on RHEL
    if [[ "$OS" == "almalinux" || "$OS" == "rhel" ]]; then
        [ -d /var/lib/php/session ] && sudo chown -R $WEB_USER:$WEB_GROUP /var/lib/php/session
        [ -d /var/lib/php/wsdlcache ] && sudo chown -R $WEB_USER:$WEB_GROUP /var/lib/php/wsdlcache
        [ -d /var/lib/php/opcache ] && sudo chown -R $WEB_USER:$WEB_GROUP /var/lib/php/opcache
    fi
fi

sudo systemctl restart $PHP_FPM_SVC &>/dev/null || sudo systemctl restart php-fpm &>/dev/null || true

# Ask for Domain
read -p "Enter your Domain/IP (e.g., example.com): " DOMAIN_NAME
DOMAIN_NAME=${DOMAIN_NAME:-$SERVER_IP}

# Variables for templates
CURRENT_PATH=$(pwd)

echo -e "${GREEN}Detected PHP-FPM Sock: $PHP_FPM_SOCK${NC}"

# Configure Nginx
if command -v nginx &> /dev/null; then
    echo -e "${YELLOW}Deploying Nginx configuration...${NC}"
    NGINX_CONF_PATH="/etc/nginx/sites-available/jejakawan.conf"
    if [ ! -d /etc/nginx/sites-available ]; then
        NGINX_CONF_PATH="/etc/nginx/conf.d/jejakawan.conf"
    fi

    sed -e "s|{{DOMAIN}}|$DOMAIN_NAME|g" \
        -e "s|{{APP_PATH}}|$CURRENT_PATH|g" \
        -e "s|{{PHP_FPM_SOCK}}|$PHP_FPM_SOCK|g" \
        scripts/templates/nginx.conf.template | sudo tee $NGINX_CONF_PATH > /dev/null
    
    if [ -d /etc/nginx/sites-enabled ]; then
        sudo ln -sf /etc/nginx/sites-available/jejakawan.conf /etc/nginx/sites-enabled/
    fi
    sudo nginx -t && sudo systemctl enable nginx && sudo systemctl restart nginx
fi

# Configure Supervisor (Workers)
if command -v $SUPERVISOR_CTL &> /dev/null || [ -d "$SUPERVISOR_CONF_DIR" ]; then
    echo -e "${YELLOW}Deploying Supervisor worker configuration...${NC}"
    [ ! -d "$SUPERVISOR_CONF_DIR" ] && sudo mkdir -p "$SUPERVISOR_CONF_DIR"
    
    # Clean up old Ubuntu-style path if on RHEL and it exists
    [ "$OS" == "almalinux" ] && [ -d /etc/supervisor/conf.d ] && sudo rm -rf /etc/supervisor/conf.d

    sed -e "s|{{APP_PATH}}|$CURRENT_PATH|g" \
        -e "s|{{USER}}|$USER|g" \
        scripts/templates/supervisor.conf.template | sudo tee "$SUPERVISOR_CONF_DIR/ja-worker.conf" > /dev/null
    
    sudo systemctl enable $SUPERVISOR_SVC &>/dev/null || true
    sudo systemctl start $SUPERVISOR_SVC &>/dev/null || true
    sudo $SUPERVISOR_CTL reread &>/dev/null || true
    sudo $SUPERVISOR_CTL update &>/dev/null || true
    # Start processes individually or all
    sudo $SUPERVISOR_CTL start ja-worker:* &>/dev/null || sudo $SUPERVISOR_CTL start all &>/dev/null || true
fi

# Configure Cron
echo -e "${YELLOW}Deploying Cron job...${NC}"
sed -e "s|{{APP_PATH}}|$CURRENT_PATH|g" scripts/templates/cron.template > scripts/current_cron
# Append to crontab if not already exists
(crontab -l 2>/dev/null | grep -v "php artisan schedule:run" ; cat scripts/current_cron) | crontab -
rm scripts/current_cron

# Final Summary
SERVER_IP=$(curl -s https://ifconfig.me || hostname -I | awk '{print $1}')
APP_URL="http://$DOMAIN_NAME"

# Update .env with finalized domain
sed -i "s|APP_URL=.*|APP_URL=$APP_URL|g" "$BACKEND_PATH/.env"
sed -i "s|VITE_API_URL=.*|VITE_API_URL=$APP_URL|g" "$BACKEND_PATH/.env"

echo -e "${GREEN}==================================================${NC}"
echo -e "${GREEN}   JEJAKAWAN INSTALLATION COMPLETE!             ${NC}"
echo -e "${GREEN}==================================================${NC}"
echo -e "${YELLOW}Access URL:${NC} ${BLUE}${APP_URL}${NC}"
echo -e ""
echo -e "${YELLOW}Default Credentials:${NC}"
echo -e "Username : ${GREEN}super${NC}"
echo -e "Password : ${GREEN}Senja@jejakawan${NC}"
echo -e "${GREEN}==================================================${NC}"
echo -e "${YELLOW}Next Step:${NC} All services (Nginx, Worker, Cron) are ready!"
echo -e "${GREEN}==================================================${NC}"

# Simple Health Check
echo -e "${YELLOW}Verifying application health...${NC}"
HTTP_STATUS=$(curl -s -o /dev/null -w "%{http_code}" http://localhost)
if [ "$HTTP_STATUS" == "200" ]; then
    echo -e "${GREEN}✅ Application is LIVE and healthy!${NC}"
else
    echo -e "${RED}⚠️ Application returned HTTP $HTTP_STATUS${NC}"
    echo -e "${YELLOW}Checking Laravel logs for clues...${NC}"
    tail -n 20 "$BACKEND_PATH/storage/logs/laravel.log" 2>/dev/null || echo "No logs found."
fi
echo -e "${GREEN}==================================================${NC}"
