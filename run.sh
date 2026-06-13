#!/usr/bin/env bash

# ==============================================================================
# PrideUnion Matrimony - App Runner
# ==============================================================================

# Color codes
GREEN='\033[0;32m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
NC='\033[0m'

echo -e "${BLUE}=====================================================${NC}"
echo -e "${CYAN}        Starting PrideUnion Matrimony Platform        ${NC}"
echo -e "${BLUE}=====================================================${NC}"

# Check if npm modules exist
if [ ! -d "node_modules" ]; then
    echo -e "Node modules missing. Running npm install first..."
    npm install
fi

# Run the Node.js application server
echo -e "${GREEN}Launching server on http://localhost:4111 ...${NC}"
node start_app.js
