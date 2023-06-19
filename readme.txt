=== Wp-Cybex-Security - Keep Your Site Protected Against Hacks ===
Contributors: Cybex Security Team
Tags: login, security, change login URL, limit attempts, failed attempts, login attempt, hack, protection, authentication, deny list, allow list, brute force, 2FA, Google Authenticator
Requires at least: 5.6
Tested up to: 6.2
Stable tag: 1.0.0
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html
 
WP CybeX Security offers a range of powerful and useful features such as login URL customisation, limit login, and two-factor authentication using Google Authenticator.
  
== Description ==
 
Our WordPress security plugin is designed to keep your site safe from a range of cyber threats. With features such as brute force and dictionary attack prevention, suspicious IP blocking, and login attempt limiting, you can rest assured that your site is protected from malicious attacks. Additionally, our plugin includes two-factor authentication using Google Authenticator to add an extra layer of security to your site’s login process. With our plugin installed, you can take a proactive approach to protecting your WordPress site and reduce the risk of unauthorised access or data breaches. Don’t leave your site’s security to chance – choose our plugin for comprehensive protection against cyber threats.
 
Our security plugin offers a range of features to protect your WordPress site from cyber threats. We provide a simple way to change your login URL which will act towards reducing bots and malicious attack attempts. Our limit login feature prevents brute force attacks by setting a limit on the number of login attempts for each user. This adds an extra layer of security to your site and ensures that unauthorised users are unable to gain access. Our two-factor authentication feature enhances security by allowing each user to manage their own profile settings in Google Authenticator settings. This means that each user can configure their own unique settings, providing an additional layer of protection to your site. Overall, our security plugin is designed to keep your WordPress site secure and protected from malicious attacks.
 
= What you can expect from WP CybeX Security: =
 
* Provides robust protection against cyber threats such as brute force and dictionary attacks.
* Offers a range of powerful and useful features such as login URL customisation, limit login, and two-factor authentication using Google Authenticator.
* Easy to install and configure, making it accessible for users with different levels of technical expertise.
* Provides top-tier support, technical assistance, and customisation services to meet all your needs.
* Enhances user confidence and trust by ensuring that your WordPress site is safe and secure from unauthorised access or data breaches.
 
== Installation ==
 
1. Upload the plugin folder to your /wp-content/plugins/ folder.
1. Go to the **Plugins** page and activate the plugin.
 
== Frequently Asked Questions ==
 
= I can’t log in using wp-admin, why? =
 
The WordPress default login URL is /wp-login. php (or you can just type in /wp-admin/ and it'll redirect you there if not yet logged in). For example: http://www.example.com/wp-login.php.
 
After this plugin is installed the WordPress default login URL will be /login. For example: http://www.example.com/login.
 
But in case you have forgotten the login URL or for any other reason you can’t log in on the website, you will need to do follow this step.
 
Advanced users:
Go to your MySQL database and look for the value of rwl_page in the options table
 
Advanced users (multisite):
Go to your MySQL database and look for the rwl_page option will be in the site meta table or options table.
 
= How to use the Redirect URL? =
 
Accessing the wp-login.php page or the wp-admin directory without logging in will redirect you to the page defined on the redirect custom field. Leaving the redirect custom field empty will activate the default settings (redirect to the website’s homepage).
 
= Automatically block IP addresses =
 
You can easily set the automatic user blockage after a certain number of failed login attempts. Moreover, you can set the period of time that the IP address will be blocked.
 
= Automatically add IP address to the Deny List =
 
You can easily set the automatic user denylisting after a certain number of blockings. This way you can be sure that there’s no way to hack your site using brute force.
 
= Send customisable notifications =
 
Notifications are the best way to always know about the changes in your list of blocked IPs. You can customise them in any way, add only the necessary information (e.g. date, time, IP, link, etc.).
 
= Easily manage your lists =
 
Both lists are fully customisable and easy to use. You can add and edit the reason for certain IPs to end up in the Block or denylist. You can also manually add or delete a single IP address from the lists or use bulk selection.
 
= How to enable 2FA? =
 
First you must download Google Authenticator on your phone/mobile device.
 
Once logged in, go to your "edit profile" section and enable 2FA by ticking "active".
 
You will be asked to scan a QR code or enter the code manually in google authenticator. If successful you will be verified and 2FA will be applied to your profile.
 
=  I want to update the secret, should I just scan the new QR code after creating a new secret? =
 
When you scan the new QR code, you will be asked to replace the existing secret if you have not deleted it already. After you scan the new code and verify, your account will be setup with the new 2FA secret.
 
=   I have several users on my WordPress installation, is that a supported configuration ? =
 
Your our security plugin offers a range of features to protect your WordPress site from cyber threats. We provide a simple way to change your login URL which will act towards reducing bots and malicious attack attempts. Our limit login feature prevents brute force attacks by setting a limit on the number of login attempts for each user. This adds an extra layer of security to your site and ensures that unauthorised users are unable to gain access. Our two-factor authentication feature enhances security by allowing each user to manage their own profile settings in Google Authenticator settings. This means that each user can configure their own unique settings, providing an additional layer of protection to your site. Overall, our security plugin is designed to keep your WordPress site secure and protected from malicious attacks.
  
== Screenshots ==
1. Change login url and redirection url.
2. Setting of limit login.
3. Set google two factor authentication in profile setting.
 
== Changelog ==
= 0.0.1 =
* Plugin released.