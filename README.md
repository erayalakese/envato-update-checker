## Envato Update Checker ##

**Envato Update Checker** is a library for WordPress theme and plugin developers. When you use this library, it downloads when plugin or theme update is available. 

HOW IT WORKS?
======
It's just a PHP class and asking for some informations about your plugin while creating an instance. 

First of all you need to create a JSON file. This file will contain latest version string of your plugin. Let's say your plugin's name is **Hello World** and **hewo** is slug. Your remote JSON file will be like this:

    {
    	"hewo":"1.1.2"
    }

This will tell to library, latest version of the *hewo* plugin is 1.1.2 . Library will compare it with your installed plugin's version and warns users if any updates available. 

Check out my remote file : http://erayalakese.com/envato-update-checker.json

INSTALLATION
======
You can use Composer to install it. 
**Composer**
If you are using Composer to manage dependencies of your WordPress plugins / themes. You can install **Envato Update Checker** via Composer.

     composer require erayalakese/envato-update-checker
     composer update

USAGE
======
If you don't have any autoloader for your Composer vendors, you should put this to your plugin's **index.php** file or theme's **functions.php** file.

     require_once(__DIR__.'/vendor/autoload.php');

Now just creating an instance of **Envato_Update_Checker** class is enough. Class constructor will do the rest. Constructor needs 5 arguments as parameter.

    new \erayalakese\Envato_Update_Checker(PLUGINNAME, PLUGINSLUG, PLUGINVERSION, REMOTEFILE, APIKEY);

**PLUGINNAME** - Name of your plugin.
**PLUGINSLUG** - alphanumerical slug of your plugin. This will used in your remote file. You can use initials of your plugin name, like *vcb*, *dmw*, ...
**PLUGINVERSION** - Installed version of your plugin. This version number will compared with your remote file.
**REMOTEFILE** - URL of your remote JSON file.
**APIKEY** - Envato API Key to verify purchases and download files from Envato. Get your APIKEY from [here](https://build.envato.com/my-apps/). Click **Register new app** button and get your API key. I recommend you to get new API KEYs for your every plugins.

EXAMPLE
========

    <?php
    /*
    Plugin Name: Test Plugin
    Plugin URI: http://eray.rocks
    Author: Eray Alakese
    Version: 1.0.0
    Author URI: http://eray.rocks
    */
    
    require_once(__DIR__.'/vendor/autoload.php');
    
    new erayalakese\Envato_Update_Checker("Test Plugin", "tp", "1.0.0", "http://erayalakese.com/envato-update-checker.json", "XYZ...ABC");
    
    /** Your plugin codes here **/



## License ##
GPL v2 - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html