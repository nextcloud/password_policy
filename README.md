# password_policy

This app enables the the admin to define certain rules for passwords, for example the minimum length of a password.

Once the app is enabled you find the "Password Policy" settings in the admin section:

![](https://github.com/nextcloud/screenshots/blob/master/password_policy/password_policy_settings.png)

By default the app enforces a minimum password length of 10 characters and checks every password against the 1.000.000 most common passwords.

Currently the app checks passwords for public link shares and for user passwords if the database backend is used.

You can easily check passwords for your own app by addind following code to your app:

````
$eventDispatcher = \OC::$server->getEventDispatcher();
$event = new Symfony\Component\EventDispatcher\GenericEvent($password);
$eventDispatcher->dispatch('OCP\PasswordPolicy::validate', $event);
````
