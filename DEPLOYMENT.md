# BICpES Learning Hub — Vercel & Supabase Deployment Guide

## Overview

This guide walks you through deploying the BICpES Learning Hub to **Vercel** with a **Supabase PostgreSQL** database.

- **Hosting**: Vercel (free tier supports PHP)
- **Database**: Supabase (free tier PostgreSQL)
- **Cost**: Free (with limitations)

---

## Prerequisites

1. **GitHub Account** — Fork/clone this repository
2. **Vercel Account** — Sign up at https://vercel.com
3. **Supabase Account** — Sign up at https://supabase.com
4. **Git** — Installed on your machine

---

## Step 1: Supabase Database Setup

### 1.1 Create a Supabase Project

1. Go to https://supabase.com and click **"New project"**
2. Choose organization, project name, and database password
3. Select region (closest to your users)
4. Wait for project to initialize (~2 min)

### 1.2 Get Database Credentials

1. Go to **Project Settings** → **Database**
2. Copy these values:
   - **Host**: `xxx.supabase.co`
   - **Port**: `5432`
   - **User**: `postgres`
   - **Password**: (the one you set)
   - **Database**: `postgres`

### 1.3 Initialize Database Schema

1. In Supabase, go to **SQL Editor**
2. Click **New query**
3. Copy the contents of `BICpES_Learning_Hub/schema_postgres.sql`
4. Paste and run

**Alternative via psql CLI:**

```bash
PGPASSWORD=your-password psql -h your-project.supabase.co -U postgres -d postgres \
  -f BICpES_Learning_Hub/schema_postgres.sql
```

### 1.4 Verify Schema

In Supabase **SQL Editor**, run:

```sql
SELECT tablename FROM pg_catalog.pg_tables WHERE schemaname = 'public';
```

You should see: `users`, `topics`, `projects`, `simulation_tools`

---

## Step 2: Prepare Code for Vercel

### 2.1 Update `db_connect.php`

The project now includes **two** database connection files:

- `db_connect.php` — Local XAMPP MySQL (development)
- `db_connect_supabase.php` — Vercel + Supabase PostgreSQL (production)

### 2.2 For Production (Vercel)

Update your PHP files to use the Supabase connection:

```php
<?php
// Use this for production (Vercel)
require_once __DIR__ . '/db_connect_supabase.php';

// Or conditionally:
if ($_SERVER['HTTP_HOST'] === 'localhost' || $_SERVER['HTTP_HOST'] === '127.0.0.1') {
    require_once __DIR__ . '/db_connect.php';  // Local MySQL
} else {
    require_once __DIR__ . '/db_connect_supabase.php';  // Supabase PostgreSQL
}
```

### 2.3 Create `.env` File (Local Reference)

```bash
cp .env.example .env
```

Edit `.env`:

```env
DB_TYPE=postgres
DB_HOST=your-project.supabase.co
DB_PORT=5432
DB_USER=postgres
DB_PASS=your-password-here
DB_NAME=postgres
APP_ENV=production
SESSION_SECURE=true
```

---

## Step 3: Deploy to Vercel

### 3.1 Connect GitHub Repository

1. Go to https://vercel.com/new
2. Click **Import Git Repository**
3. Select this repository (`SoftwareDesign`)
4. Click **Import**

### 3.2 Configure Environment Variables

In Vercel dashboard:

1. Go to **Settings** → **Environment Variables**
2. Add the following variables:

| Variable | Value |
|---|---|
| `DB_TYPE` | `postgres` |
| `DB_HOST` | `your-project.supabase.co` |
| `DB_PORT` | `5432` |
| `DB_USER` | `postgres` |
| `DB_PASS` | *(your Supabase password)* |
| `DB_NAME` | `postgres` |
| `APP_ENV` | `production` |
| `SESSION_SECURE` | `true` |

### 3.3 Configure Build Settings

1. **Framework Preset**: Select **Other**
2. **Build Command**: Leave empty (or `echo 'PHP ready'`)
3. **Output Directory**: Leave empty
4. **Install Command**: Leave empty

### 3.4 Deploy

Click **Deploy** and wait (~2-3 min)

---

## Step 4: Post-Deployment Verification

### 4.1 Check Live Site

1. Vercel provides a URL: `https://your-project.vercel.app`
2. Visit: `https://your-project.vercel.app/index.php`
3. You should see the BICpES Learning Hub homepage

### 4.2 Test Login

- **Admin Login**: ID = `ADMIN`, Password = `password` (change immediately!)
- **Student Registration**: Create a test account

### 4.3 Check Database Connectivity

In the admin dashboard (`admin_dashboard.php`), try:
- Creating a new topic
- Creating a new project
- Verify data appears

---

## Troubleshooting

### Issue: "Database connection failed"

**Solution:**
1. Verify Supabase credentials in Vercel environment variables
2. Check Supabase is running (Project page shows green status)
3. Verify SSL certificate (Supabase requires it)

### Issue: "CORS errors" or "SSL certificate problem"

**Solution:**
Add SSL context to PDO:

```php
'stream_context' => stream_context_create(['ssl' => ['verify_peer' => true]])
```

### Issue: "PHP files return 404"

**Solution:**
Vercel requires `vercel.json` routing configuration (already included).

### Issue: "Sessions not persisting"

**Solution:**
Set `SESSION_SECURE=true` only if your domain has HTTPS (Vercel provides this).

---

## Local Development

### Development with XAMPP (MySQL)

```bash
cd BICpES_Learning_Hub
php -S localhost:8000
```

Then visit: http://localhost:8000/index.php

This uses `db_connect.php` (MySQL) automatically.

### Development with Supabase (Testing production setup)

Edit `db_connect.php` to include Supabase credentials temporarily:

```php
define('DB_HOST',    'your-project.supabase.co');
define('DB_USER',    'postgres');
define('DB_PASS',    'your-password');
define('DB_NAME',    'postgres');
```

---

## Security Notes

⚠️ **Important:**

1. **Never commit `.env` file** to Git (add to `.gitignore`)
2. **Change admin password immediately** after first login
3. **Use strong passwords** for Supabase
4. **Enable Row Level Security (RLS)** in Supabase for production
5. **Set up HTTPS** (automatic with Vercel)
6. **Backup your database** regularly via Supabase console

---

## Next Steps

1. Customize admin password
2. Add your projects and topics
3. Upload sample images
4. Configure custom domain (Vercel → Settings → Domains)
5. Monitor project usage (Supabase/Vercel free tier limits)

---

## Support & Resources

- **Vercel Docs**: https://vercel.com/docs
- **Supabase Docs**: https://supabase.com/docs
- **PostgreSQL**: https://www.postgresql.org/docs/

---

**Last Updated**: May 28, 2026
