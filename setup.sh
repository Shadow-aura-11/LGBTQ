#!/usr/bin/env bash

# ==============================================================================
# PrideUnion Matrimony - Ubuntu Setup Script
# ==============================================================================

# Exit immediately if a command exits with a non-zero status
set -e

# Color codes for clean interface output
RED='\033[0;31m'
GREEN='\033[0;32m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

echo -e "${BLUE}=====================================================${NC}"
echo -e "${CYAN}   PrideUnion Matrimony - Ubuntu Installation Script  ${NC}"
echo -e "${BLUE}=====================================================${NC}"

# Check if OS is Ubuntu/Debian-based
if [ -f /etc/os-release ]; then
    . /etc/os-release
    if [[ "$ID" != "ubuntu" && "$ID_LIKE" != *"ubuntu"* && "$ID" != "debian" ]]; then
        echo -e "${RED}[WARNING] This script is optimized for Ubuntu/Debian. Continuing anyway...${NC}"
    fi
else
    echo -e "${RED}[ERROR] Operating System information not found. Make sure you are on Ubuntu.${NC}"
    exit 1
fi

echo -e "\n${CYAN}[1/5] Updating package list...${NC}"
sudo apt-get update -y

# Check and Install Node.js & npm
echo -e "\n${CYAN}[2/5] Setting up Node.js & npm...${NC}"
if ! command -v node &> /dev/null; then
    echo -e "Node.js is not installed. Installing Node.js LTS..."
    curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
    sudo apt-get install -y nodejs
else
    echo -e "${GREEN}Node.js is already installed ($(node -v)).${NC}"
fi

# Check and Install PHP & Composer (optional but useful for the decupled microservices)
echo -e "\n${CYAN}[3/5] Setting up PHP & Composer...${NC}"
if ! command -v php &> /dev/null; then
    echo -e "PHP is not installed. Installing PHP 8.2..."
    sudo apt-get install -y php-cli php-common php-mysql php-xml php-curl php-mbstring php-zip php-gd php-redis
else
    echo -e "${GREEN}PHP is already installed ($(php -v | head -n 1)).${NC}"
fi

if ! command -v composer &> /dev/null; then
    echo -e "Composer is not installed. Installing Composer..."
    curl -sS https://getcomposer.org/installer | php
    sudo mv composer.phar /usr/local/bin/composer
else
    echo -e "${GREEN}Composer is already installed ($(composer --version | head -n 1)).${NC}"
fi

# Configure Environment
echo -e "\n${CYAN}[4/5] Configuring Environment Files...${NC}"
if [ ! -f .env ]; then
    if [ -f .env.example ]; then
        cp .env.example .env
        echo -e "${GREEN}Created .env file from template.${NC}"
    else
        echo -e "${RED}[WARNING] .env.example not found. Creating a blank .env file.${NC}"
        touch .env
    fi
else
    echo -e "${GREEN}.env file already exists. Skipping copy.${NC}"
fi

# Install dependencies
echo -e "\n${CYAN}[5/5] Installing App Dependencies...${NC}"
if [ -f package.json ]; then
    echo -e "Installing npm dependencies..."
    npm install
fi

if [ -f composer.json ]; then
    echo -e "Installing composer dependencies..."
    composer install --no-dev --optimize-autoloader || echo -e "${RED}[NOTICE] Composer install skipped or completed with warnings.${NC}"
fi

# Ensure uploads directory exists and is writable
mkdir -p uploads scratch
chmod -R 775 uploads scratch || true

# Set execute permissions on run.sh
if [ -f run.sh ]; then
    chmod +x run.sh
fi

echo -e "\n${GREEN}=====================================================${NC}"
echo -e "${GREEN}              SETUP COMPLETED SUCCESSFULLY!          ${NC}"
echo -e "${GREEN}=====================================================${NC}"
echo -e "You can now start the application by running:"
echo -e "${CYAN}  ./run.sh${NC}\n"
