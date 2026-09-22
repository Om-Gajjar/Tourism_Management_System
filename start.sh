#!/bin/bash
#
# Tourism Management System - Start Script
# Starts MariaDB, Apache (phpMyAdmin), and PHP built-in server
#
# Usage: ./start.sh [start|stop|restart|status]
#
# Services:
#   - MariaDB (database)        -> localhost:3306
#   - Apache (phpMyAdmin)       -> http://localhost/phpmyadmin
#   - PHP built-in (website)    -> http://localhost:8000

# Configuration
PROJECT_DIR="/home/kali/tourism-project"
PHP_PORT=8000
PHP_HOST="localhost"
DB_PORT=3306
PMA_URL="http://localhost/phpmyadmin"
LOG_DIR="$PROJECT_DIR/logs"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Logging function
log() {
    echo -e "${BLUE}[$(date '+%Y-%m-%d %H:%M:%S')]${NC} $1"
}

log_success() {
    echo -e "${GREEN}[$(date '+%Y-%m-%d %H:%M:%S')] ✓${NC} $1"
}

log_error() {
    echo -e "${RED}[$(date '+%Y-%m-%d %H:%M:%S')] ✗${NC} $1"
}

log_warning() {
    echo -e "${YELLOW}[$(date '+%Y-%m-%d %H:%M:%S')] ⚠${NC} $1"
}

# Check if running as appropriate user
check_permissions() {
    if [ "$EUID" -ne 0 ] && [ "$1" = "stop" ]; then
        # For stop, we need sudo for MySQL
        :
    fi
}

# Ensure log directory exists
ensure_log_dir() {
    if [ ! -d "$LOG_DIR" ]; then
        mkdir -p "$LOG_DIR"
        log "Created log directory: $LOG_DIR"
    fi
}

# Check if Apache is running
is_apache_running() {
    if sudo service apache2 status > /dev/null 2>&1; then
        return 0
    fi
    
    # Fallback: check if Apache process is running
    if pgrep -x "apache2" > /dev/null 2>&1 || pgrep -f "httpd" > /dev/null 2>&1; then
        return 0
    fi
    
    return 1
}

# Check if MariaDB is running
is_mysql_running() {
    # First check via service status
    if sudo service mysql status > /dev/null 2>&1; then
        return 0
    fi
    
    # Fallback: check if MySQL process is running
    if pgrep -x "mysqld" > /dev/null 2>&1 || pgrep -f "mariadbd" > /dev/null 2>&1; then
        return 0
    fi
    
    return 1
}

# Check if PHP server is running
is_php_running() {
    # Check if something is listening on the PHP port
    if command -v ss &> /dev/null; then
        ss -tlnp | grep -q ":$PHP_PORT"
        return $?
    elif command -v netstat &> /dev/null; then
        netstat -tlnp | grep -q ":$PHP_PORT"
        return $?
    else
        # Fallback: try curl
        curl -s -o /dev/null "http://$PHP_HOST:$PHP_PORT" 2>/dev/null
        return $?
    fi
}

# Get PHP server PID
get_php_pid() {
    pgrep -f "php -S $PHP_HOST:$PHP_PORT" 2>/dev/null | head -1
}

# Start MariaDB
start_mysql() {
    log "Checking MariaDB..."
    
    if is_mysql_running; then
        log_success "MariaDB is already running"
        return 0
    fi
    
    log "Starting MariaDB..."
    sudo service mysql start 2>&1
    
    # Wait for MySQL to be ready using service status
    local retries=15
    while [ $retries -gt 0 ]; do
        if is_mysql_running; then
            log_success "MariaDB started successfully"
            return 0
        fi
        sleep 1
        ((retries--))
    done
    
    log_error "MariaDB failed to start after 15 seconds"
    return 1
}

# Start Apache (for phpMyAdmin)
start_apache() {
    log "Checking Apache (phpMyAdmin)..."
    
    if is_apache_running; then
        log_success "Apache is already running"
        return 0
    fi
    
    log "Starting Apache..."
    sudo service apache2 start 2>&1
    
    # Wait for Apache to be ready
    local retries=10
    while [ $retries -gt 0 ]; do
        if is_apache_running; then
            # Verify phpMyAdmin is accessible
            if curl -s -o /dev/null "$PMA_URL" 2>/dev/null; then
                log_success "Apache started successfully"
                log "phpMyAdmin: $PMA_URL"
                return 0
            fi
        fi
        sleep 1
        ((retries--))
    done
    
    # Apache might be running but phpMyAdmin not yet accessible
    if is_apache_running; then
        log_success "Apache started (phpMyAdmin may need a moment)"
        return 0
    fi
    
    log_error "Apache failed to start"
    return 1
}

# Start PHP server
start_php() {
    log "Checking PHP server on $PHP_HOST:$PHP_PORT..."
    
    if is_php_running; then
        local pid=$(get_php_pid)
        log_success "PHP server is already running (PID: $pid)"
        return 0
    fi
    
    log "Starting PHP server..."
    ensure_log_dir
    
    cd "$PROJECT_DIR" || {
        log_error "Cannot change to project directory: $PROJECT_DIR"
        return 1
    }
    
    # Start PHP server in background with router.php, logging to file
    nohup php -S "$PHP_HOST:$PHP_PORT" router.php >> "$LOG_DIR/php_server.log" 2>&1 &
    local php_pid=$!
    
    # Wait for server to be ready
    local retries=10
    while [ $retries -gt 0 ]; do
        if curl -s -o /dev/null "http://$PHP_HOST:$PHP_PORT" 2>/dev/null; then
            log_success "PHP server started successfully (PID: $php_pid)"
            log "Server URL: http://$PHP_HOST:$PHP_PORT"
            log "Logs: $LOG_DIR/php_server.log"
            return 0
        fi
        sleep 0.5
        ((retries--))
    done
    
    log_error "PHP server failed to start"
    return 1
}

# Stop PHP server
stop_php() {
    log "Stopping PHP server..."
    
    local pid=$(get_php_pid)
    
    if [ -z "$pid" ]; then
        log "PHP server is not running"
        return 0
    fi
    
    kill $pid 2>/dev/null
    
    # Wait for process to stop
    local retries=5
    while [ $retries -gt 0 ]; do
        if ! kill -0 $pid 2>/dev/null; then
            log_success "PHP server stopped"
            return 0
        fi
        sleep 1
        ((retries--))
    done
    
    # Force kill if still running
    log_warning "Force killing PHP server..."
    kill -9 $pid 2>/dev/null
    log_success "PHP server stopped"
    return 0
}

# Stop MariaDB
stop_mysql() {
    log "Stopping MariaDB..."
    
    if ! is_mysql_running; then
        log "MariaDB is not running"
        return 0
    fi
    
    sudo service mysql stop 2>&1
    
    if ! is_mysql_running; then
        log_success "MariaDB stopped"
        return 0
    fi
    
    log_error "Failed to stop MariaDB"
    return 1
}

# Stop Apache
stop_apache() {
    log "Stopping Apache..."
    
    if ! is_apache_running; then
        log "Apache is not running"
        return 0
    fi
    
    sudo service apache2 stop 2>&1
    
    if ! is_apache_running; then
        log_success "Apache stopped"
        return 0
    fi
    
    log_error "Failed to stop Apache"
    return 1
}

# Show status
show_status() {
    echo ""
    echo "=============================================="
    echo "  Tourism Management System - Status"
    echo "=============================================="
    echo ""
    
    # MariaDB status
    echo -n "MariaDB (database): "
    if is_mysql_running; then
        echo -e "${GREEN}✓ Running${NC} (port $DB_PORT)"
    else
        echo -e "${RED}✗ Not Running${NC}"
    fi
    
    # Apache/phpMyAdmin status
    echo -n "Apache (phpMyAdmin): "
    if is_apache_running; then
        echo -e "${GREEN}✓ Running${NC}"
    else
        echo -e "${RED}✗ Not Running${NC}"
    fi
    
    # PHP server status
    echo -n "PHP Server ($PHP_HOST:$PHP_PORT): "
    if is_php_running; then
        local pid=$(get_php_pid)
        echo -e "${GREEN}✓ Running${NC} (PID: $pid)"
    else
        echo -e "${RED}✗ Not Running${NC}"
    fi
    
    # Project directory
    echo ""
    echo "Project Directory: $PROJECT_DIR"
    echo "Logs: $LOG_DIR"
    echo ""
    
    # URLs
    echo "Access Points:"
    echo "  Website:   http://$PHP_HOST:$PHP_PORT"
    echo "  phpMyAdmin: $PMA_URL"
    echo ""
    
    # Quick health check
    echo "Quick Health Check:"
    if curl -s -o /dev/null -w "  Homepage: HTTP %{http_code}\n" "http://$PHP_HOST:$PHP_PORT" 2>/dev/null; then
        :
    else
        echo "  Homepage: Cannot connect"
    fi
    
    if curl -s -o /dev/null -w "  phpMyAdmin: HTTP %{http_code}\n" "$PMA_URL" 2>/dev/null; then
        :
    else
        echo "  phpMyAdmin: Cannot connect"
    fi
    
    if curl -s -o /dev/null -w "  CSS: HTTP %{http_code}\n" "http://$PHP_HOST:$PHP_PORT/css/style.css" 2>/dev/null; then
        :
    else
        echo "  CSS: Cannot connect"
    fi
    
    echo ""
}

# Main command handler
case "$1" in
    start)
        echo ""
        log "Starting Tourism Management System..."
        echo ""
        
        start_mysql
        if [ $? -ne 0 ]; then
            log_error "MariaDB failed to start. Aborting."
            exit 1
        fi
        
        echo ""
        start_apache
        if [ $? -ne 0 ]; then
            log_warning "Apache failed to start, but continuing..."
        fi
        
        echo ""
        start_php
        if [ $? -ne 0 ]; then
            log_error "PHP server failed to start."
            exit 1
        fi
        
        echo ""
        log_success "Tourism Management System is ready!"
        echo ""
        log "Access the website at: http://$PHP_HOST:$PHP_PORT"
        log "Access phpMyAdmin at: $PMA_URL"
        echo ""
        ;;
        
    stop)
        echo ""
        log "Stopping Tourism Management System..."
        echo ""
        
        stop_php
        stop_apache
        stop_mysql
        
        echo ""
        log_success "Tourism Management System stopped"
        echo ""
        ;;
        
    restart)
        echo ""
        log "Restarting Tourism Management System..."
        echo ""
        
        stop_php
        sleep 1
        
        start_mysql
        if [ $? -ne 0 ]; then
            log_error "MariaDB failed to start."
            exit 1
        fi
        
        start_apache
        if [ $? -ne 0 ]; then
            log_warning "Apache failed to start, but continuing..."
        fi
        
        start_php
        if [ $? -ne 0 ]; then
            log_error "PHP server failed to start."
            exit 1
        fi
        
        echo ""
        log_success "Tourism Management System restarted"
        echo ""
        ;;
        
    status)
        show_status
        ;;
        
    *)
        echo "Tourism Management System - Start Script"
        echo ""
        echo "Usage: $0 [command]"
        echo ""
        echo "Commands:"
        echo "  start   - Start MariaDB, Apache (phpMyAdmin), and PHP server"
        echo "  stop    - Stop PHP server, Apache, and MariaDB"
        echo "  restart - Restart all services"
        echo "  status  - Show current status"
        echo ""
        echo "Services:"
        echo "  - MariaDB (database)     -> localhost:$DB_PORT"
        echo "  - Apache (phpMyAdmin)    -> $PMA_URL"
        echo "  - PHP (website)          -> http://$PHP_HOST:$PHP_PORT"
        echo ""
        echo "Examples:"
        echo "  $0 start"
        echo "  $0 status"
        echo "  $0 stop"
        echo ""
        ;;
esac

exit 0
