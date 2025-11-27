# Deployment Guide - Render.com

This guide explains how to deploy the Ticketing Application to Render.com. The application consists of:

1. **Backend API** - Laravel 12 REST API
2. **Frontend** - Vue 3 SPA
3. **Database** - PostgreSQL
4. **Queue Worker** - Background job processor

## Prerequisites

- A Render.com account
- GitHub repository connected to Render
- Payment gateway credentials (FedaPay, PayDunya, CinetPay, MTN MoMo)

## Deployment Methods

### Method 1: Using render.yaml (Recommended)

The repository includes a `render.yaml` file that automates the entire deployment process.

1. **Connect Repository to Render**
   - Go to [Render Dashboard](https://dashboard.render.com/)
   - Click "New +" → "Blueprint"
   - Connect your GitHub repository
   - Select the branch to deploy

2. **Render will automatically create:**
   - PostgreSQL database (`ticketing-db`)
   - Backend API service (`ticketing-api`)
   - Queue worker service (`ticketing-queue`)
   - Frontend static site (`ticketing-frontend`)

3. **Configure Environment Variables**

   After deployment, you need to set the following environment variables:

   **Backend API & Queue Worker:**
   - `MAIL_HOST` - Your SMTP server
   - `MAIL_USERNAME` - SMTP username
   - `MAIL_PASSWORD` - SMTP password
   - `FEDAPAY_PUBLIC_KEY` - FedaPay public key
   - `FEDAPAY_SECRET_KEY` - FedaPay secret key
   - `FEDAPAY_WEBHOOK_SECRET` - FedaPay webhook secret
   - `FEDAPAY_ENVIRONMENT` - `sandbox` or `production`

   **Frontend:**
   - `VITE_PAYDUNYA_API_KEY` - PayDunya API key (optional)
   - `VITE_CINETPAY_API_KEY` - CinetPay API key (optional)
   - `VITE_CINETPAY_SITE_ID` - CinetPay Site ID (optional)
   - `VITE_MTN_MOMO_API_KEY` - MTN MoMo API key (optional)

### Method 2: Manual Setup

If you prefer to set up services manually:

#### 1. Create PostgreSQL Database

1. Go to Render Dashboard → "New +" → "PostgreSQL"
2. Configure:
   - Name: `ticketing-db`
   - Database: `ticketing`
   - User: `ticketing`
   - Region: Choose your preferred region
   - Plan: Starter (or higher)

#### 2. Deploy Backend API

1. Go to Render Dashboard → "New +" → "Web Service"
2. Configure:
   - **Name:** `ticketing-api`
   - **Root Directory:** `ticketing-api-rest-app`
   - **Environment:** Docker
   - **Region:** Same as database
   - **Branch:** Your main branch
   - **Build Command:** `./deploy.sh`
   - **Start Command:** `php artisan serve --host=0.0.0.0 --port=$PORT`
   - **Plan:** Starter (or higher)

3. Add Environment Variables (see list above)

4. Add Health Check:
   - Path: `/api/health`

#### 3. Deploy Queue Worker

1. Go to Render Dashboard → "New +" → "Background Worker"
2. Configure:
   - **Name:** `ticketing-queue`
   - **Root Directory:** `ticketing-api-rest-app`
   - **Environment:** Docker
   - **Region:** Same as database
   - **Branch:** Your main branch
   - **Build Command:** `./deploy.sh`
   - **Start Command:** `php artisan queue:work --tries=3 --timeout=90`

3. Add the same environment variables as the Backend API

#### 4. Deploy Frontend

1. Go to Render Dashboard → "New +" → "Static Site"
2. Configure:
   - **Name:** `ticketing-frontend`
   - **Root Directory:** Leave empty (root)
   - **Build Command:** `cd ticketing-app && npm install && npm run build`
   - **Publish Directory:** `ticketing-app/dist`

3. Add Environment Variables:
   - `VITE_API_URL` - URL of your backend API (e.g., `https://ticketing-api.onrender.com/api`)
   - See frontend environment variables above

4. Add Rewrite Rule for SPA:
   - Source: `/*`
   - Destination: `/index.html`

## Post-Deployment Steps

### 1. Verify Backend Health

Check that the API is running:
```bash
curl https://your-api-url.onrender.com/api/health
```

### 2. Create Admin User (Optional)

You may need to create an admin user. Connect to your backend shell and run:

```bash
php artisan tinker
```

Then create a user:
```php
$user = new App\Models\User();
$user->name = 'Admin';
$user->email = 'admin@example.com';
$user->password = bcrypt('your-secure-password');
$user->email_verified_at = now();
$user->save();
```

### 3. Configure FedaPay Webhooks

1. Log in to your FedaPay dashboard
2. Go to Settings → Webhooks
3. Add webhook URL: `https://your-api-url.onrender.com/api/webhooks/fedapay`
4. Copy the webhook secret to `FEDAPAY_WEBHOOK_SECRET` environment variable

### 4. Test Payment Integration

1. Visit your frontend URL
2. Create a test event
3. Purchase a ticket using the sandbox payment gateway
4. Verify the payment is processed correctly

### 5. Enable Production Mode

Once everything is tested:

1. Update `FEDAPAY_ENVIRONMENT` to `production`
2. Update payment gateway credentials to production keys
3. Set `APP_DEBUG` to `false` (should already be set)
4. Set `VITE_BETA_FEATURES` to `false` if needed

## Monitoring & Maintenance

### Logs

- **Backend Logs:** Render Dashboard → Your service → Logs
- **Queue Worker Logs:** Render Dashboard → Queue worker → Logs
- **Database Logs:** Render Dashboard → Database → Logs

### Database Backups

Render automatically backs up PostgreSQL databases on paid plans. You can also manually backup:

1. Go to Database service → Backups
2. Click "Create Backup"

### Scaling

If you experience high traffic:

1. **Upgrade Plans:** Move to higher-tier plans for more resources
2. **Add Queue Workers:** Duplicate the queue worker service for parallel processing
3. **Add Redis:** Consider adding Redis for caching and sessions

## Troubleshooting

### Issue: "500 Internal Server Error"

1. Check logs in Render Dashboard
2. Verify all environment variables are set
3. Ensure database connection is working
4. Check APP_KEY is generated

### Issue: "Database connection failed"

1. Verify database environment variables are correct
2. Check database is in the same region as web service
3. Ensure database is running

### Issue: "Queue jobs not processing"

1. Check queue worker logs
2. Verify queue worker is running
3. Check database connection for queue worker
4. Ensure QUEUE_CONNECTION is set to `database`

### Issue: "Frontend can't connect to API"

1. Verify `VITE_API_URL` is set correctly with `/api` suffix
2. Check CORS settings in backend
3. Ensure backend is running and healthy

## Environment Variables Reference

### Backend API & Queue Worker

| Variable | Required | Description | Default |
|----------|----------|-------------|---------|
| `APP_NAME` | Yes | Application name | Ticketing API |
| `APP_ENV` | Yes | Environment | production |
| `APP_KEY` | Yes | Encryption key | Auto-generated |
| `APP_DEBUG` | Yes | Debug mode | false |
| `APP_URL` | Yes | Application URL | Auto-set |
| `DB_CONNECTION` | Yes | Database type | pgsql |
| `DB_HOST` | Yes | Database host | Auto-set |
| `DB_PORT` | Yes | Database port | Auto-set |
| `DB_DATABASE` | Yes | Database name | Auto-set |
| `DB_USERNAME` | Yes | Database user | Auto-set |
| `DB_PASSWORD` | Yes | Database password | Auto-set |
| `MAIL_MAILER` | Yes | Mail driver | smtp |
| `MAIL_HOST` | Yes | SMTP host | - |
| `MAIL_PORT` | Yes | SMTP port | 587 |
| `MAIL_USERNAME` | No | SMTP username | - |
| `MAIL_PASSWORD` | No | SMTP password | - |
| `MAIL_FROM_ADDRESS` | Yes | Sender email | noreply@ticketing-app.com |
| `FEDAPAY_PUBLIC_KEY` | Yes | FedaPay public key | - |
| `FEDAPAY_SECRET_KEY` | Yes | FedaPay secret key | - |
| `FEDAPAY_WEBHOOK_SECRET` | Yes | FedaPay webhook secret | - |
| `FEDAPAY_ENVIRONMENT` | Yes | FedaPay environment | sandbox |
| `FEDAPAY_CURRENCY` | Yes | Currency code | XOF |

### Frontend

| Variable | Required | Description | Default |
|----------|----------|-------------|---------|
| `VITE_API_URL` | Yes | Backend API URL | Auto-set |
| `VITE_PAYDUNYA_API_KEY` | No | PayDunya API key | - |
| `VITE_CINETPAY_API_KEY` | No | CinetPay API key | - |
| `VITE_CINETPAY_SITE_ID` | No | CinetPay Site ID | - |
| `VITE_MTN_MOMO_API_KEY` | No | MTN MoMo API key | - |
| `VITE_ENABLE_MULTI_ORG` | No | Enable multi-org | true |
| `VITE_ENABLE_CASH_PAYMENTS` | No | Enable cash payments | true |
| `VITE_BETA_FEATURES` | No | Enable beta features | false |
| `NODE_ENV` | Yes | Node environment | production |

## Cost Estimate

Based on Render's pricing (as of 2024):

- **Database (Starter):** $7/month
- **Backend API (Starter):** $7/month
- **Queue Worker (Starter):** $7/month
- **Frontend (Static Site):** Free

**Total:** ~$21/month (plus bandwidth costs)

For production, consider upgrading to higher tiers for better performance and reliability.

## Support

For issues related to:
- **Deployment:** Check Render documentation at https://render.com/docs
- **Application:** Check the repository README files
- **Payment Integration:** Contact respective payment gateway support

## Security Best Practices

1. **Never commit sensitive data** to the repository
2. **Use environment variables** for all secrets and API keys
3. **Enable HTTPS** (automatically enabled by Render)
4. **Keep dependencies updated** regularly
5. **Monitor logs** for suspicious activities
6. **Use strong passwords** for admin accounts
7. **Enable two-factor authentication** on Render account
