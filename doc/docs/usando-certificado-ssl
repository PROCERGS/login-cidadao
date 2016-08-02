How To Secure Apache with Let's Encrypt on Ubuntu 14.04
Posted Dec 18, 2015 180.7k views Security Apache Let's Encrypt Ubuntu
Introduction

This tutorial will show you how to set up a TLS/SSL certificate from Let’s Encrypt on an Ubuntu 14.04 server running Apache as a web server. We will also cover how to automate the certificate renewal process using a cron job.

SSL certificates are used within web servers to encrypt the traffic between the server and client, providing extra security for users accessing your application. Let’s Encrypt provides an easy way to obtain and install trusted certificates for free.
Prerequisites

In order to complete this guide, you will need:

    An Ubuntu 14.04 server with a non-root sudo user, which you can set up by following our Initial Server Setup guide
    The Apache web server installed with one or more domain names properly configured

When you are ready to move on, log into your server using your sudo account.
Step 1 — Install the Server Dependencies

The first thing we need to do is to update the package manager cache with:

    sudo apt-get update

We will need git in order to download the Let’s Encrypt client. To install git, run:

    sudo apt-get install git 

Step 2 — Download the Let’s Encrypt Client

Next, we will download the Let’s Encrypt client from its official repository, placing its files in a special location on the server. We will do this to facilitate the process of updating the repository files when a new release is available. Because the Let’s Encrypt client is still in beta, frequent updates might be necessary to correct bugs and implement new functionality.

We will clone the Let’s Encrypt repository under /opt, which is a standard directory for placing third-party software on Unix systems:

    sudo git clone https://github.com/letsencrypt/letsencrypt /opt/letsencrypt

This will create a local copy of the official Let’s Encrypt repository under /opt/letsencrypt.
Step 3 — Set Up the SSL Certificate

Generating the SSL Certificate for Apache using the Let’s Encrypt client is quite straightforward. The client will automatically obtain and install a new SSL certificate that is valid for the domains provided as parameters.

Access the letsencrypt directory:

    cd /opt/letsencrypt

To execute the interactive installation and obtain a certificate that covers only a single domain, run the letsencrypt-auto command with:

    ./letsencrypt-auto --apache -d example.com

If you want to install a single certificate that is valid for multiple domains or subdomains, you can pass them as additional parameters to the command. The first domain name in the list of parameters will be the base domain used by Let’s Encrypt to create the certificate, and for that reason we recommend that you pass the bare top-level domain name as first in the list, followed by any additional subdomains or aliases:

    ./letsencrypt-auto --apache -d example.com -d www.example.com

For this example, the base domain will be example.com.

After the dependencies are installed, you will be presented with a step-by-step guide to customize your certificate options. You will be asked to provide an email address for lost key recovery and notices, and you will be able to choose between enabling both http and https access or force all requests to redirect to https.

When the installation is finished, you should be able to find the generated certificate files at /etc/letsencrypt/live. You can verify the status of your SSL certificate with the following link (don’t forget to replace example.com with your base domain):

https://www.ssllabs.com/ssltest/analyze.html?d=example.com&latest

You should now be able to access your website using a https prefix.
Step 4 — Set Up Auto Renewal

Let’s Encrypt certificates are valid for 90 days, but it’s recommended that you renew the certificates every 60 days to allow a margin of error. The Let's Encrypt client has a renew command that automatically checks the currently installed certificates and tries to renew them if they are less than 30 days away from the expiration date.

To trigger the renewal process for all installed domains, you should run:

./letsencrypt-auto renew

Because we recently installed the certificate, the command will only check for the expiration date and print a message informing that the certificate is not due to renewal yet. The output should look similar to this:

Checking for new version...
Requesting root privileges to run letsencrypt...
   /root/.local/share/letsencrypt/bin/letsencrypt renew
Processing /etc/letsencrypt/renewal/example.com.conf

The following certs are not due for renewal yet:
  /etc/letsencrypt/live/example.com/fullchain.pem (skipped)
No renewals were attempted.

Notice that if you created a bundled certificate with multiple domains, only the base domain name will be shown in the output, but the renewal should be valid for all domains included in this certificate.

A practical way to ensure your certificates won’t get outdated is to create a cron job that will periodically execute the automatic renewal command for you. Since the renewal first checks for the expiration date and only executes the renewal if the certificate is less than 30 days away from expiration, it is safe to create a cron job that runs every week or even every day, for instance.

Let's edit the crontab to create a new job that will run the renewal command every week. To edit the crontab for the root user, run:

    sudo crontab -e

Include the following content, all in one line:

crontab

30 2 * * 1 /opt/letsencrypt/letsencrypt-auto renew >> /var/log/le-renew.log

Save and exit. This will create a new cron job that will execute the letsencrypt-auto renew command every Monday at 2:30 am. The output produced by the command will be piped to a log file located at /var/log/le-renewal.log.

For more information on how to create and schedule cron jobs, you can check our How to Use Cron to Automate Tasks in a VPS guide.
Step 5 — Updating the Let’s Encrypt Client (optional)

Whenever new updates are available for the client, you can update your local copy by running a git pull from inside the Let’s Encrypt directory:

    cd /opt/letsencrypt
    sudo git pull

This will download all recent changes to the repository, updating your client.
Conclusion

In this guide, we saw how to install a free SSL certificate from Let’s Encrypt in order to secure a website hosted with Apache. Because the Let’s Encrypt client is still in beta, we recommend that you check the official Let’s Encrypt blog for important updates from time to time.

source: https://www.digitalocean.com/community/tutorials/how-to-secure-apache-with-let-s-encrypt-on-ubuntu-14-04
