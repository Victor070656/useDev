#!/bin/bash

##############################################################################
# DevAllies Setup Checklist Script
# This script helps verify your installation is ready for production
##############################################################################

echo "========================================="
echo "DevAllies Platform Setup Verification"
echo "========================================="
echo ""

# Colors for output
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Track overall status
all_passed=true

# Function to check status
check_status() {
    if [ $1 -eq 0 ]; then
        echo -e "${GREEN}✓${NC} $2"
    else
        echo -e "${RED}✗${NC} $2"
        all_passed=false
    fi
}

# Function to check warning
check_warning() {
    echo -e "${YELLOW}⚠${NC} $1"
}

echo "1. Checking Directory Permissions..."
echo "-----------------------------------"

# Check upload directories
for dir in uploads/profiles uploads/portfolio uploads/attachments; do
    if [ -d "$dir" ] && [ -w "$dir" ]; then
        check_status 0 "$dir is writable"
    else
        check_status 1 "$dir is NOT writable or doesn't exist"
    fi
done

echo ""
echo "2. Checking Required PHP Extensions..."
echo "---------------------------------------"

# Check PHP extensions
php -m | grep -q "gd" && check_status 0 "GD library installed" || check_status 1 "GD library NOT installed"
php -m | grep -q "mysqli" && check_status 0 "MySQLi extension installed" || check_status 1 "MySQLi NOT installed"
php -m | grep -q "curl" && check_status 0 "cURL extension installed" || check_status 1 "cURL NOT installed"
php -m | grep -q "json" && check_status 0 "JSON extension installed" || check_status 1 "JSON NOT installed"

echo ""
echo "3. Checking Configuration Files..."
echo "----------------------------------"

# Check if config files exist
[ -f "includes/config.php" ] && check_status 0 "config.php exists" || check_status 1 "config.php NOT found"
[ -f "includes/upload_handler.php" ] && check_status 0 "upload_handler.php exists" || check_status 1 "upload_handler.php NOT found"
[ -f "includes/paystack_helper.php" ] && check_status 0 "paystack_helper.php exists" || check_status 1 "paystack_helper.php NOT found"
[ -f "includes/contract_helpers.php" ] && check_status 0 "contract_helpers.php exists" || check_status 1 "contract_helpers.php NOT found"
[ -f "includes/transaction_helpers.php" ] && check_status 0 "transaction_helpers.php exists" || check_status 1 "transaction_helpers.php NOT found"

echo ""
echo "4. Checking Paystack Configuration..."
echo "--------------------------------------"

# Check if Paystack keys are configured (not default)
if grep -q "pk_test_\.\.\." includes/config.php; then
    check_warning "Paystack PUBLIC_KEY is still using default placeholder"
else
    check_status 0 "Paystack PUBLIC_KEY appears configured"
fi

if grep -q "sk_test_\.\.\." includes/config.php; then
    check_warning "Paystack SECRET_KEY is still using default placeholder"
else
    check_status 0 "Paystack SECRET_KEY appears configured"
fi

# Check Paystack mode
if grep -q "define('PAYSTACK_MODE', 'test')" includes/config.php; then
    check_status 0 "Paystack is in TEST mode (recommended for initial setup)"
elif grep -q "define('PAYSTACK_MODE', 'live')" includes/config.php; then
    check_warning "Paystack is in LIVE mode - make sure you've tested thoroughly!"
fi

echo ""
echo "5. Checking Database Connection..."
echo "----------------------------------"

# Try to connect to database
if /opt/lampp/bin/mysql -u root -e "USE devallies; SELECT 1;" &>/dev/null; then
    check_status 0 "Database 'devallies' is accessible"

    # Check if transactions table has paystack support
    if /opt/lampp/bin/mysql -u root -e "USE devallies; DESCRIBE transactions;" | grep -q "paystack"; then
        check_status 0 "Transactions table supports Paystack"
    else
        check_status 1 "Transactions table does NOT support Paystack (run migration)"
    fi
else
    check_status 1 "Cannot connect to database 'devallies'"
fi

echo ""
echo "6. Checking Upload Endpoints..."
echo "--------------------------------"

# Check if upload endpoints exist
[ -f "creator/upload-profile-picture.php" ] && check_status 0 "Profile picture upload endpoint exists" || check_status 1 "Profile picture upload endpoint NOT found"
[ -f "creator/upload-portfolio-image.php" ] && check_status 0 "Portfolio image upload endpoint exists" || check_status 1 "Portfolio image upload endpoint NOT found"
[ -f "client/upload-brief-attachment.php" ] && check_status 0 "Brief attachment upload endpoint exists" || check_status 1 "Brief attachment upload endpoint NOT found"
[ -f "messages/upload-attachment.php" ] && check_status 0 "Message attachment upload endpoint exists" || check_status 1 "Message attachment upload endpoint NOT found"

echo ""
echo "7. Checking Payment Endpoints..."
echo "---------------------------------"

# Check payment endpoints
[ -f "client/initiate-payment.php" ] && check_status 0 "Payment initiation endpoint exists" || check_status 1 "Payment initiation endpoint NOT found"
[ -f "client/payment-callback.php" ] && check_status 0 "Payment callback endpoint exists" || check_status 1 "Payment callback endpoint NOT found"
[ -f "creator/request-payout.php" ] && check_status 0 "Payout request endpoint exists" || check_status 1 "Payout request endpoint NOT found"
[ -f "webhooks/paystack.php" ] && check_status 0 "Webhook handler exists" || check_status 1 "Webhook handler NOT found"
[ -f "admin/process-refund.php" ] && check_status 0 "Refund handler exists" || check_status 1 "Refund handler NOT found"

echo ""
echo "8. Checking Milestone Endpoints..."
echo "-----------------------------------"

# Check milestone endpoints
[ -f "client/create-milestone.php" ] && check_status 0 "Create milestone endpoint exists" || check_status 1 "Create milestone endpoint NOT found"
[ -f "client/approve-milestone.php" ] && check_status 0 "Approve milestone endpoint exists" || check_status 1 "Approve milestone endpoint NOT found"
[ -f "creator/submit-milestone.php" ] && check_status 0 "Submit milestone endpoint exists" || check_status 1 "Submit milestone endpoint NOT found"

echo ""
echo "9. Checking Transaction Pages..."
echo "---------------------------------"

# Check transaction pages
[ -f "creator/transactions.php" ] && check_status 0 "Transaction history page exists" || check_status 1 "Transaction history page NOT found"
[ -f "creator/receipt.php" ] && check_status 0 "Receipt page exists" || check_status 1 "Receipt page NOT found"

echo ""
echo "10. Checking Documentation..."
echo "------------------------------"

# Check documentation files
[ -f "IMPLEMENTATION_SUMMARY.md" ] && check_status 0 "Implementation summary exists" || check_status 1 "Implementation summary NOT found"
[ -f "PAYSTACK_SETUP_GUIDE.md" ] && check_status 0 "Paystack setup guide exists" || check_status 1 "Paystack setup guide NOT found"
[ -f "QUICK_START.md" ] && check_status 0 "Quick start guide exists" || check_status 1 "Quick start guide NOT found"

echo ""
echo "========================================="
echo "Setup Verification Complete!"
echo "========================================="
echo ""

if [ "$all_passed" = true ]; then
    echo -e "${GREEN}✓ All checks passed!${NC}"
    echo ""
    echo "Next steps:"
    echo "1. Review PAYSTACK_SETUP_GUIDE.md for Paystack configuration"
    echo "2. Update Paystack API keys in includes/config.php"
    echo "3. Set up webhook URL in Paystack dashboard"
    echo "4. Test all flows using test cards"
    echo "5. Review QUICK_START.md for testing procedures"
else
    echo -e "${RED}✗ Some checks failed. Please review the output above.${NC}"
    echo ""
    echo "Common fixes:"
    echo "- Run: chmod 755 uploads/profiles uploads/portfolio uploads/attachments"
    echo "- Update includes/config.php with your Paystack keys"
    echo "- Run database migration for transactions table"
    echo "- Ensure all required PHP extensions are installed"
fi

echo ""
echo "For detailed setup instructions, see:"
echo "- IMPLEMENTATION_SUMMARY.md"
echo "- PAYSTACK_SETUP_GUIDE.md"
echo "- QUICK_START.md"
echo ""
