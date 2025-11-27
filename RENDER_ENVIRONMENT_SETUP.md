# Render Environment Variables Setup Guide

This guide provides the exact values you need to configure in Render.com after deploying your application.

## 🚨 IMPORTANT: Security Notice

**DO NOT commit your actual .env file to the repository!** The `.env` file is already in `.gitignore`, but always verify before committing.

## How to Set Environment Variables in Render

1. Go to your [Render Dashboard](https://dashboard.render.com/)
2. Click on the service (e.g., "ticketing-api")
3. Go to "Environment" tab
4. Click "Add Environment Variable"
5. Enter the key and value
6. Click "Save Changes" (this will trigger a redeploy)

## Environment Variables to Configure

### 1. Backend API Service (`ticketing-api`)

After deployment, you need to manually set these environment variables:

#### Application URLs
```
APP_URL=https://your-api-name.onrender.com
CLIENT_APP_URL=https://your-frontend-name.onrender.com
```
> **Note:** Replace with your actual Render service URLs after deployment

#### Mail Configuration (SMTP)
```
MAIL_HOST=smtp.titan.email
MAIL_USERNAME=your-smtp-username@example.com
MAIL_PASSWORD=your-smtp-password
MAIL_FROM_ADDRESS=your-email@example.com
```
> **Note:** Use the values from your current `.env` file

#### SMS Provider (e-mc.co)
```
SMS_PROVIDER_URL=https://api.e-mc.co/v3
SMS_PROVIDER_API_ACCOUNT_ID=your-sms-account-id
SMS_PROVIDER_API_ACCOUNT_PASSWORD=your-sms-account-password
SMS_PROVIDER_API_KEY=your-sms-api-key
```
> **Note:** Use the values from your current `.env` file

#### Alert Configuration
```
ALERT_EMAIL=email1@example.com,email2@example.com
ALERT_SMS=phone1,phone2
```
> **Note:** Use the values from your current `.env` file

#### Laravel Passport OAuth Credentials
```
PASSPORT_GRANT_ACCESS_CLIENT_ID=your-grant-access-client-id
PASSPORT_GRANT_ACCESS_CLIENT_SECRET=your-grant-access-client-secret
PASSPORT_PERSONAL_ACCESS_CLIENT_ID=your-personal-access-client-id
PASSPORT_PERSONAL_ACCESS_CLIENT_SECRET=your-personal-access-client-secret
```
> **Note:** Use the values from your current `.env` file

#### CinetPay Configuration (Production)
```
CINETPAY_API_KEY=your-cinetpay-api-key
CINETPAY_SITE_ID=your-cinetpay-site-id
CINETPAY_SECRET_KEY=your-cinetpay-secret-key
```
> **Note:** Use the values from your current `.env` file

#### FedaPay Configuration (Live)
```
FEDAPAY_PUBLIC_KEY=pk_live_xxxxxxxxxxxxx
FEDAPAY_SECRET_KEY=sk_live_xxxxxxxxxxxxx
FEDAPAY_WEBHOOK_SECRET=wh_live_xxxxxxxxxxxxx
```
> **Note:** Use the values from your current `.env` file

### 2. Queue Worker Service (`ticketing-queue`)

The Queue Worker needs the same environment variables as the Backend API. Set all the variables listed above for the queue worker service as well.

### 3. Frontend Service (`ticketing-frontend`)

#### API URL Configuration
```
VITE_API_URL=https://your-api-name.onrender.com/api
```
> **Important:** Make sure to include `/api` at the end of the URL
> **Note:** Replace with your actual backend API URL after deployment

#### CinetPay (for frontend payment integration)
```
VITE_CINETPAY_API_KEY=your-cinetpay-api-key
VITE_CINETPAY_SITE_ID=your-cinetpay-site-id
```
> **Note:** Use the same CinetPay credentials from your backend `.env` file

#### Optional Payment Gateways
```
VITE_PAYDUNYA_API_KEY=your-paydunya-api-key (if you use PayDunya)
VITE_MTN_MOMO_API_KEY=your-mtn-momo-api-key (if you use MTN MoMo)
```

## Auto-Configured Variables

The following variables are automatically set by Render (you don't need to set these manually):

### Backend API & Queue Worker
- ✅ `APP_KEY` - Auto-generated encryption key
- ✅ `TOKEN_ENCRYPTION_KEY` - Auto-generated
- ✅ `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` - Auto-set from database

### Frontend
- ⚠️ All frontend environment variables must be set manually (Render doesn't auto-populate static site env vars)

## Step-by-Step Setup Process

### Step 1: Deploy Using Blueprint

1. Go to [Render Dashboard](https://dashboard.render.com/)
2. Click "New +" → "Blueprint"
3. Connect your GitHub repository
4. Select branch: `claude/deploy-ren-environment-0139xhC4fcY4J1SJuqfrXYyK`
5. Click "Apply"
6. Wait for initial deployment (it will fail because environment variables are missing)

### Step 2: Note Your Service URLs

After the initial deployment (which may fail due to missing env vars), note your service URLs:

1. Go to "ticketing-api" service → Copy the URL (e.g., `https://ticketing-api-xxxx.onrender.com`)
2. Go to "ticketing-frontend" service → Copy the URL (e.g., `https://ticketing-frontend-xxxx.onrender.com`)

### Step 3: Configure Backend API Environment Variables

1. Go to "ticketing-api" service
2. Click "Environment" tab
3. Add all the environment variables listed in section 1 above:
   - Set `APP_URL` to your backend API URL
   - Set `CLIENT_APP_URL` to your frontend URL
   - Set all other variables from your `.env` file
4. Click "Save Changes" (this triggers a redeploy)

### Step 4: Configure Queue Worker Environment Variables

1. Go to "ticketing-queue" service
2. Click "Environment" tab
3. Add the same environment variables as the Backend API (including APP_URL and CLIENT_APP_URL)
4. Click "Save Changes"

### Step 5: Configure Frontend Environment Variables

1. Go to "ticketing-frontend" service
2. Click "Environment" tab
3. Add the frontend environment variables:
   - Set `VITE_API_URL` to your backend API URL + `/api` (e.g., `https://ticketing-api-xxxx.onrender.com/api`)
   - Set payment gateway variables (VITE_CINETPAY_*, etc.)
4. Click "Save Changes"

### Step 6: Verify Deployment

1. Wait for all services to finish deploying (check the "Events" tab)
2. Check Backend API health:
   ```bash
   curl https://your-api-url.onrender.com/api/health
   ```
   Should return:
   ```json
   {
     "status": "healthy",
     "database": "connected",
     "timestamp": "2024-11-27T..."
   }
   ```

3. Visit your frontend URL to test the application

### Step 7: Configure Webhooks

#### FedaPay Webhook Setup
1. Log in to your FedaPay dashboard
2. Go to Settings → Webhooks
3. Add webhook URL: `https://your-api-url.onrender.com/api/webhooks/fedapay`
4. The webhook secret should already be set in your environment variables

#### CinetPay Webhook Setup (if needed)
1. Log in to your CinetPay dashboard
2. Configure callback URL to point to your Render backend

### Step 8: Test Payment Integration

1. Visit your frontend application
2. Create a test event
3. Try purchasing a ticket
4. Verify the payment flow works correctly
5. Check that tickets are generated with QR codes
6. Test QR code scanning functionality

## Troubleshooting

### Issue: Services Keep Failing After Deployment

**Solution:** Check that ALL required environment variables are set. Missing any of the critical variables (especially mail, SMS, or payment gateway credentials) can cause the services to fail.

### Issue: Database Connection Errors

**Solution:** Ensure that:
1. The database service is fully deployed and running
2. The database environment variables are auto-populated correctly
3. Try manually redeploying the backend API service

### Issue: Queue Jobs Not Processing

**Solution:**
1. Check queue worker logs in Render Dashboard
2. Verify all environment variables are set for the queue worker
3. Ensure the queue worker service is running
4. Check database connection for the queue worker

### Issue: Frontend Can't Connect to Backend

**Solution:**
1. Verify `VITE_API_URL` is correctly set in frontend environment
2. The URL should include `/api` at the end (e.g., `https://ticketing-api.onrender.com/api`)
3. Check CORS settings in backend
4. Ensure backend is healthy by checking the `/api/health` endpoint

### Issue: Payments Not Working

**Solution:**
1. Verify all payment gateway credentials are correctly set
2. Check that `FEDAPAY_ENVIRONMENT` is set to `live` (not `sandbox`)
3. Check that `CINETPAY_MODE` is set to `production`
4. Verify webhook URLs are correctly configured in payment gateway dashboards
5. Check backend logs for payment processing errors

### Issue: SMS Not Sending

**Solution:**
1. Verify all SMS provider credentials are correctly set
2. Check that SMS provider API is accessible from Render servers
3. Verify SMS balance/credits with your provider
4. Check backend logs for SMS sending errors

## Environment Variables Checklist

Use this checklist to ensure all variables are set:

### Backend API (ticketing-api) ✓
- [ ] APP_URL
- [ ] CLIENT_APP_URL
- [ ] MAIL_HOST
- [ ] MAIL_USERNAME
- [ ] MAIL_PASSWORD
- [ ] MAIL_FROM_ADDRESS
- [ ] SMS_PROVIDER_URL
- [ ] SMS_PROVIDER_API_ACCOUNT_ID
- [ ] SMS_PROVIDER_API_ACCOUNT_PASSWORD
- [ ] SMS_PROVIDER_API_KEY
- [ ] ALERT_EMAIL
- [ ] ALERT_SMS
- [ ] PASSPORT_GRANT_ACCESS_CLIENT_ID
- [ ] PASSPORT_GRANT_ACCESS_CLIENT_SECRET
- [ ] PASSPORT_PERSONAL_ACCESS_CLIENT_ID
- [ ] PASSPORT_PERSONAL_ACCESS_CLIENT_SECRET
- [ ] CINETPAY_API_KEY
- [ ] CINETPAY_SITE_ID
- [ ] CINETPAY_SECRET_KEY
- [ ] FEDAPAY_PUBLIC_KEY
- [ ] FEDAPAY_SECRET_KEY
- [ ] FEDAPAY_WEBHOOK_SECRET

### Queue Worker (ticketing-queue) ✓
- [ ] Same as Backend API (copy all variables)

### Frontend (ticketing-frontend) ✓
- [ ] VITE_API_URL
- [ ] VITE_CINETPAY_API_KEY
- [ ] VITE_CINETPAY_SITE_ID
- [ ] VITE_PAYDUNYA_API_KEY (optional)
- [ ] VITE_MTN_MOMO_API_KEY (optional)

## Security Best Practices

1. ✅ **Never commit sensitive credentials** to your repository
2. ✅ **Use environment variables** for all secrets
3. ✅ **Rotate credentials regularly** (change passwords, API keys periodically)
4. ✅ **Monitor logs** for suspicious activities
5. ✅ **Enable two-factor authentication** on your Render account
6. ✅ **Use strong passwords** for all admin accounts
7. ✅ **Keep dependencies updated** (run `composer update` and `npm update` regularly)
8. ✅ **Set up monitoring** (use Render's built-in logging and consider external monitoring services)

## Production Deployment Checklist

Before going live with production traffic:

- [ ] All environment variables are correctly set
- [ ] Health check endpoint returns "healthy" status
- [ ] Database migrations have run successfully
- [ ] Test payment flow with real payment (small amount)
- [ ] Verify email sending works
- [ ] Verify SMS sending works
- [ ] Test ticket generation and QR code creation
- [ ] Test QR code scanning at gates
- [ ] Configure payment gateway webhooks
- [ ] Set up monitoring and alerts
- [ ] Take a database backup
- [ ] Document any custom configurations
- [ ] Test error handling and edge cases
- [ ] Verify mobile responsiveness
- [ ] Test all user roles and permissions

## Monitoring and Maintenance

### Regular Tasks

1. **Daily:** Check logs for errors in Render Dashboard
2. **Weekly:** Review database usage and performance
3. **Monthly:**
   - Check for security updates
   - Update dependencies
   - Review and optimize database queries
   - Check storage usage
4. **Quarterly:**
   - Rotate sensitive credentials
   - Review access controls
   - Performance optimization

### Backup Strategy

Render automatically backs up PostgreSQL databases on paid plans. Additionally:

1. Set up automated daily backups
2. Test restore procedures regularly
3. Keep at least 7 days of backups
4. Consider exporting critical data to external storage

## Cost Management

Monitor your usage to avoid unexpected costs:

1. Check bandwidth usage monthly
2. Monitor database size (upgrade plan if needed)
3. Review compute usage for optimization opportunities
4. Consider upgrading to higher tiers for better performance as your user base grows

## Support and Resources

- **Render Documentation:** https://render.com/docs
- **FedaPay Documentation:** https://fedapay.com/developers
- **CinetPay Documentation:** https://docs.cinetpay.com
- **Laravel Documentation:** https://laravel.com/docs
- **Vue.js Documentation:** https://vuejs.org/guide

## Contact

For application-specific issues:
- Check the repository's issue tracker
- Review application logs in Render Dashboard
- Contact your development team

For Render platform issues:
- Render Support: https://render.com/support
- Render Status: https://status.render.com
