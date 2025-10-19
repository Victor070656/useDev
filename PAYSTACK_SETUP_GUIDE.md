# Paystack Setup Guide for DevAllies

This guide will help you configure Paystack payment integration for the DevAllies platform.

---

## Table of Contents
1. [Create Paystack Account](#1-create-paystack-account)
2. [Get API Keys](#2-get-api-keys)
3. [Configure Application](#3-configure-application)
4. [Setup Webhook](#4-setup-webhook)
5. [Test the Integration](#5-test-the-integration)
6. [Go Live](#6-go-live)

---

## 1. Create Paystack Account

### Step 1: Sign Up
1. Go to https://paystack.com/
2. Click "Get Started" or "Sign Up"
3. Fill in your business details:
   - Business Name: Your company name
   - Email: Business email address
   - Password: Strong password
4. Verify your email address
5. Complete business verification (KYC)

### Step 2: Business Verification
You'll need to provide:
- Business registration documents
- Business address
- Bank account details
- Owner identification (ID card, passport, etc.)

**Note**: Verification can take 1-3 business days.

---

## 2. Get API Keys

### Test Mode Keys (for development)
1. Log in to https://dashboard.paystack.com/
2. Go to **Settings** → **API Keys & Webhooks**
3. Under "Test Mode", you'll see:
   - **Public Key**: `pk_test_...`
   - **Secret Key**: `sk_test_...` (Click "Show" to reveal)
4. Copy both keys

### Live Mode Keys (for production)
1. After your business is verified
2. Go to **Settings** → **API Keys & Webhooks**
3. Under "Live Mode", you'll see:
   - **Public Key**: `pk_live_...`
   - **Secret Key**: `sk_live_...`
4. Copy both keys

⚠️ **Security**: Never commit your secret keys to version control!

---

## 3. Configure Application

### Step 1: Update Configuration File
Open `/includes/config.php` and update:

```php
// For Testing
define('PAYSTACK_ENABLED', true);
define('PAYSTACK_PUBLIC_KEY', 'pk_test_YOUR_KEY_HERE');
define('PAYSTACK_SECRET_KEY', 'sk_test_YOUR_KEY_HERE');
define('PAYSTACK_MODE', 'test');

// For Production (after testing)
define('PAYSTACK_ENABLED', true);
define('PAYSTACK_PUBLIC_KEY', 'pk_live_YOUR_KEY_HERE');
define('PAYSTACK_SECRET_KEY', 'sk_live_YOUR_KEY_HERE');
define('PAYSTACK_MODE', 'live');
```

### Step 2: Update Currency Conversion Rate
Open `/includes/paystack_helper.php` and update the exchange rate (around line 230):

```php
public static function convertCurrency($cents, $fromCurrency = 'USD', $toCurrency = 'NGN') {
    if ($fromCurrency === 'USD' && $toCurrency === 'NGN') {
        // Update this rate regularly - current rate as of Oct 2025
        $exchangeRate = 1500; // 1 USD = 1500 NGN (example)
        return $cents * $exchangeRate;
    }
    return $cents;
}
```

**Get current rate from**: https://www.xe.com/ or https://www.google.com/search?q=usd+to+ngn

---

## 4. Setup Webhook

Webhooks allow Paystack to notify your application about payment events automatically.

### Step 1: Configure Webhook URL
1. Go to https://dashboard.paystack.com/
2. Navigate to **Settings** → **API Keys & Webhooks**
3. Scroll down to "Webhook URL"
4. Enter your webhook URL:
   ```
   https://yourdomain.com/webhooks/paystack.php
   ```
   Replace `yourdomain.com` with your actual domain

5. Click "Save Changes"

### Step 2: Test Webhook (Optional but Recommended)
1. In the same webhook settings page
2. Click "Test Webhook"
3. Select an event type (e.g., `charge.success`)
4. Click "Send Test"
5. Check your application logs to verify it was received

### Step 3: Subscribe to Events
Make sure these events are enabled:
- ✅ `charge.success` - Payment successful
- ✅ `transfer.success` - Payout successful
- ✅ `transfer.failed` - Payout failed
- ✅ `transfer.reversed` - Payout reversed

---

## 5. Test the Integration

### Test Cards
Use these test cards in test mode:

#### Successful Payment
- **Card Number**: `4084084084084081`
- **CVV**: `408`
- **Expiry Date**: Any future date
- **PIN**: `0000`
- **OTP**: `123456`

#### Declined Payment
- **Card Number**: `5060666666666666666`
- **CVV**: Any 3 digits
- **Expiry Date**: Any future date

#### Insufficient Funds
- **Card Number**: `5060666666666666666`
- (Will prompt for insufficient funds error)

### Test Flow

#### A. Test Payment (Client Side)
1. Log in as a client
2. Go to a contract or project brief
3. Click "Pay Now" or "Make Payment"
4. Use test card: `4084084084084081`
5. Complete the payment flow
6. Verify you're redirected back with success message
7. Check transaction appears in transaction history

#### B. Test Payout (Creator Side)
1. Log in as a creator
2. Ensure you have pending payout balance
3. Go to Earnings or Transactions page
4. Click "Request Payout"
5. Enter bank details:
   - **Bank**: Select any Nigerian bank
   - **Account Number**: Use test account: `0123456789`
   - **Account Name**: Your name
6. Submit payout request
7. Verify payout status updates

#### C. Test Webhook
1. Make a test payment
2. Check activity logs table: `SELECT * FROM activity_logs WHERE action LIKE '%webhook%'`
3. Verify webhook events are being received
4. Check transaction status updates automatically

### Database Verification
Run these queries to verify data:

```sql
-- Check transactions
SELECT * FROM transactions ORDER BY created_at DESC LIMIT 10;

-- Check contracts with payments
SELECT c.*, t.status as payment_status
FROM contracts c
LEFT JOIN transactions t ON c.id = t.contract_id
WHERE t.transaction_type = 'payment';

-- Check milestones
SELECT * FROM contract_milestones WHERE status = 'paid';
```

---

## 6. Go Live

### Pre-Launch Checklist
Before switching to live mode:

- [ ] Business is fully verified on Paystack
- [ ] All test flows working correctly
- [ ] Webhook receiving events properly
- [ ] Exchange rate is up to date
- [ ] Error logging is working
- [ ] Email notifications configured (optional)
- [ ] Terms of service updated with payment terms
- [ ] Privacy policy updated with payment data handling
- [ ] Customer support process in place

### Switch to Live Mode

1. **Update API Keys** in `/includes/config.php`:
   ```php
   define('PAYSTACK_PUBLIC_KEY', 'pk_live_YOUR_LIVE_KEY');
   define('PAYSTACK_SECRET_KEY', 'sk_live_YOUR_LIVE_KEY');
   define('PAYSTACK_MODE', 'live');
   ```

2. **Update Webhook URL** (if domain changed):
   - Go to Paystack Dashboard
   - Update webhook URL to production domain
   - Test the webhook

3. **Clear Test Data** (optional):
   ```sql
   -- Backup first!
   DELETE FROM transactions WHERE payment_provider = 'paystack' AND status = 'pending';
   ```

4. **Monitor First Transactions**:
   - Watch logs closely
   - Verify payments process correctly
   - Check webhook events are received
   - Test payout flow with real bank account

---

## Troubleshooting

### Issue: Payments fail immediately
**Solutions**:
- Verify API keys are correct
- Check if Paystack is enabled in config
- Review error logs: `/opt/lampp/htdocs/useDev2/logs/error.log`
- Verify internet connection from server

### Issue: Callback page not loading
**Solutions**:
- Check callback URL is publicly accessible
- Verify no authentication blocking callback page
- Check server error logs
- Test callback URL directly in browser

### Issue: Webhook not receiving events
**Solutions**:
- Verify webhook URL is correct in Paystack dashboard
- Check webhook URL is accessible (use curl to test)
- Verify webhook signature validation
- Check server firewall settings
- Review activity logs: `SELECT * FROM activity_logs WHERE action LIKE '%webhook%'`

### Issue: Payout fails
**Solutions**:
- Verify bank account details are correct
- Check creator has sufficient balance
- Verify bank code is valid Nigerian bank
- Check Paystack account balance (for transfers)
- Review Paystack dashboard for error messages

### Issue: Currency conversion issues
**Solutions**:
- Update exchange rate in `/includes/paystack_helper.php`
- Verify amounts are in correct units (cents/kobo)
- Check database amounts are stored in cents

---

## Testing Commands

### Test Database Transactions
```sql
-- View all payment transactions
SELECT * FROM transactions
WHERE transaction_type = 'payment'
ORDER BY created_at DESC;

-- View pending payouts
SELECT * FROM transactions
WHERE transaction_type = 'payout' AND status = 'pending';

-- View completed payments with contract info
SELECT t.*, c.contract_amount, pb.title
FROM transactions t
JOIN contracts c ON t.contract_id = c.id
JOIN project_briefs pb ON c.project_brief_id = pb.id
WHERE t.status = 'completed';
```

### Test Webhook Locally
Use ngrok or similar to expose local server:
```bash
# Install ngrok
wget https://bin.equinox.io/c/4VmDzA7iaHb/ngrok-stable-linux-amd64.zip
unzip ngrok-stable-linux-amd64.zip

# Expose local server
./ngrok http 80

# Use the generated URL in Paystack webhook settings
# Example: https://abc123.ngrok.io/webhooks/paystack.php
```

### Check Payment Provider Support
```php
// Add to test page
<?php
require_once 'includes/init.php';
require_once 'includes/paystack_helper.php';

$paystack = get_paystack();
$banks = $paystack->getBanks('nigeria');

echo '<pre>';
print_r($banks);
echo '</pre>';
?>
```

---

## Paystack Fees

### Transaction Fees (as of 2025)
- **Local Cards**: 1.5% capped at ₦2,000
- **International Cards**: 3.9% + ₦100
- **Bank Transfer**: ₦50 flat fee

### Transfer Fees (Payouts)
- **Free transfers**: First 50 transfers per month
- **After 50 transfers**: ₦10 per transfer

**Note**: Fees may change. Check https://paystack.com/pricing for current rates.

---

## Support Resources

### Paystack Support
- **Email**: support@paystack.com
- **Twitter**: @PaystackHQ
- **Documentation**: https://paystack.com/docs/
- **Status Page**: https://status.paystack.com/

### DevAllies Platform Support
- **Error Logs**: `/opt/lampp/htdocs/useDev2/logs/error.log`
- **Database Logs**: `activity_logs` table
- **Transaction History**: Check via admin dashboard

---

## Security Best Practices

### 1. Protect Your Secret Keys
```bash
# Never commit keys to git
echo "includes/config.php" >> .gitignore

# Use environment variables (optional)
export PAYSTACK_SECRET_KEY="sk_live_..."
export PAYSTACK_PUBLIC_KEY="pk_live_..."
```

### 2. Validate Webhook Signatures
The webhook handler already validates signatures:
```php
// In /webhooks/paystack.php
if (!$paystack->validateWebhookSignature($input, $signature)) {
    http_response_code(401);
    exit('Invalid signature');
}
```

### 3. Use HTTPS in Production
- Always use HTTPS for payment pages
- Redirect HTTP to HTTPS
- Update callback URLs to use HTTPS

### 4. Log All Transactions
- All transactions are logged in database
- Activity logs track all payment actions
- Review logs regularly for suspicious activity

### 5. Implement Rate Limiting
Consider adding rate limiting for payment endpoints:
- Limit payment attempts per user per hour
- Limit payout requests per day
- Monitor for unusual patterns

---

## FAQs

### Q: Can I use Paystack outside Nigeria?
**A**: Yes, Paystack supports businesses in Nigeria, Ghana, and South Africa. However, payout features work best for Nigerian banks.

### Q: What currencies does Paystack support?
**A**: NGN (Nigerian Naira), GHS (Ghanaian Cedi), ZAR (South African Rand), and USD.

### Q: How long do payouts take?
**A**: Usually instant to a few hours. In some cases up to 24 hours for bank transfers.

### Q: Can I test without a Paystack account?
**A**: No, you need a Paystack account to get test API keys.

### Q: What's the minimum payout amount?
**A**: Currently set to $50 (5000 cents) in the platform. You can adjust in `/includes/config.php`:
```php
define('MINIMUM_PAYOUT', 5000); // $50.00 in cents
```

### Q: How do I handle disputes?
**A**: Disputes are handled through Paystack dashboard. You'll receive notifications and can respond through the platform.

---

## Next Steps

After completing setup:
1. ✅ Test all payment flows thoroughly
2. ✅ Test webhook notifications
3. ✅ Test payout system
4. ✅ Review transaction logs
5. ✅ Set up monitoring/alerts
6. ✅ Train support team
7. ✅ Create user documentation
8. ✅ Go live!

---

**Need Help?**
- Review the [IMPLEMENTATION_SUMMARY.md](IMPLEMENTATION_SUMMARY.md) for technical details
- Check Paystack documentation: https://paystack.com/docs/
- Contact Paystack support: support@paystack.com

---

*Last Updated: October 15, 2025*
