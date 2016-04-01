Env (Milkyway Multimedia)
============================================
Dot notation access of configuration, as well as overrides via $_ENV (environment variables)

singleton('env')
----------------
This is the new way to access configuration. Instead of using `Config::inst()->get('Email', 'admin_email')`
you can now use `singleton('env')->get('Email.admin_email')`. But that is not its real feature.

### Features
1. Access deep array configuration: `singleton('env')->get('Email.site_emails.staff')`
2. Override configuration using $_ENV variables, good for when you are developing locally
3. Use configuration fallbacks: `singleton('env')->get('FacebookPage|Facebook.admin_id')`
     - Will check FacebookPage, and then check Facebook
4. Add callbacks to check in between namespaces
5. Cached

vlucas/phpdotenv
----------------
For development, you can use a **.env.php** file to override variables, but it requires some manual installation.

You need to install the package: vlucas/phpdotenv via composer.json, and then follow the instructions in that package.

If you want the environment to work, you should add the following to your `ss_environment.php`:

```
   require_once BASE_PATH . '/mwm-env/code/dev/Environment.php';
```


## Install
Add the following to your composer.json file

```

    "require"          : {
		"milkyway-multimedia/ss-mwm-env": "~0.3"
	}

```

## License
* MIT

## Version
* Version 0.3 (Alpha)

## Contact
#### Mellisa Hankins
* E-mail: mellisa.hankins@me.com
* Twitter: [@mi3ll](https://twitter.com/mi3ll "mi3ll on twitter")
* Website: mellimade.com.au
