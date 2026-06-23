# EMI-Locker: Oracle Cloud Live Deployment Guide

This guide contains the complete A-to-Z roadmap for hosting the EMI-Locker Laravel backend on the internet for **FREE** using Oracle Cloud's "Always Free" tier.

## Step 1: Create an Oracle Cloud Account
1. Go to the official website: [Oracle Cloud Free Tier](https://www.oracle.com/cloud/free/) and click **"Start for free"**.
2. Fill in your basic details. Ensure you choose your **Home Region** carefully (e.g., *Mumbai* or *Hyderabad*), as it cannot be changed later.
3. **Card Verification:** Enter your Credit or Debit card details. Oracle will deduct a small amount (approx. ₹80 - ₹100) for identity verification, which will be **refunded instantly**. *You will never be billed unless you manually upgrade to a paid account.*

## Step 2: Create Your "Always Free" Server (VM Instance)
1. Once logged into the dashboard, navigate to **Compute > Instances** and click **"Create a VM instance"**.
2. Give your instance a name (e.g., `emi-locker-backend`).
3. Under **Image and Shape**, click **Edit**:
   *   **Image (OS):** Select `Ubuntu 22.04` or `24.04`.
   *   **Shape:** Choose `Ampere (ARM)`. Set the CPU to **4 Cores** and RAM to **24 GB**. (You should see an "Always Free Eligible" tag).
4. Under **Add SSH keys**, choose **"Save Private Key"** and download it to your PC. *(Important: Do not lose this file, you need it to log into your server).*
5. Click **Create**. Within 1-2 minutes, your server will be running, and you will see your **Public IP Address**.

## Step 3: Open Network Ports (Crucial Step)
Oracle Cloud blocks web traffic by default. You need to open ports 80 and 443.
1. On your Instance details page, click on the **Subnet** link.
2. Click on the **Default Security List**.
3. Click **Add Ingress Rules**.
4. Set the **Destination Port Range** to `80,443`.
5. Click **Add Ingress Rules** to save.

## Step 4: Connect to the Server & Install aaPanel
We will install **aaPanel**, a free web hosting control panel with a visual interface (similar to Laragon/cPanel), so you don't have to manage things purely via the black terminal screen.
1. Open your PC's Command Prompt/Terminal.
2. Connect to the server using the private key you downloaded:
   ```bash
   ssh -i "path/to/your/private-key.key" ubuntu@YOUR_SERVER_PUBLIC_IP
   ```
3. Once logged in, switch to the root user:
   ```bash
   sudo su
   ```
4. Run the aaPanel installation script (Ubuntu):
   ```bash
   URL=https://www.aapanel.com/script/install_6.0_en.sh && echo y | bash $URL aapanel
   ```
5. The installation will take 5-10 minutes. Once done, it will print a **Login URL, Username, and Password** on the screen. Save these details!

## Step 5: Upload Code & Go Live!
1. Log in to the aaPanel dashboard using the details from the previous step.
2. A popup will ask you to install a software stack. Choose **LNMP** (Nginx, MySQL, and PHP). Make sure to select **PHP 8.2**. Click One-Click Install.
3. Go to the **Websites** tab and click **Add Site**. Put in your server IP (or your Domain Name if you bought one) and hit submit.
4. Go to the **Files** section in aaPanel, navigate to your newly created website directory, and upload a `.zip` file of your local `c:\laragon\www\emi-locker\backend` folder. Extract it.
5. In the aaPanel **Databases** tab, create a new MySQL database. Upload/import your local Laragon database SQL file.
6. Edit the `.env` file in the file manager:
   *   Update `DB_DATABASE`, `DB_USERNAME`, and `DB_PASSWORD` with the new aaPanel database credentials.
   *   Change `APP_ENV=production` and `APP_DEBUG=false`.
7. **Change Document Root:** In the aaPanel Website settings, go to Site Directory and change the "Running Directory" to `/public` (because Laravel serves from the public folder).
8. Save everything.

**Congratulations!** Your EMI-Locker backend is now live 24/7 on the internet. You can update the Android app to point to this new live IP/Domain, build the APK, and sell it to clients.
