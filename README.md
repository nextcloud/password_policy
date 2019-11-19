# Password policy

This app enables the the admin to define certain rules for passwords, for example the minimum length of a password.

Once the app is enabled you find the "Password policy" settings in the admin section:

![](https://github.com/nextcloud/screenshots/blob/master/password_policy/password_policy_settings.png)

By default the app enforces a minimum password length of 10 characters and checks every password against the 1.000.000 most common passwords.

Currently the app checks passwords for public link shares and for user passwords if the database backend is used.

You can easily check passwords for your own app by adding following code to your app:

````
$eventDispatcher = \OC::$server->query(IEventDispatcher::class);
$event = new \OCP\Security\Events\GenerateSecurePasswordEvent();
$eventDispatcher->dispatchTyped($event);
$password = $event->getPassword() ?? 'fallback when this app is not enabled';
````
